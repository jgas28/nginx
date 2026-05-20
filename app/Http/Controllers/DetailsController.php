<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryRequest;
use App\Models\DeliveryRequestLineItem;
use App\Models\CashVoucher;
use App\Models\Liquidation;
use Illuminate\Support\Collection;

class DetailsController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = DeliveryRequest::query()
            ->with([
                'company:id,company_code,company_name',
                'customer:id,name',
            ])
            ->withCount([
                'lineItems',
                'cashVouchers',
                'cvrApprovals',
                'liquidations',
            ])
            ->withSum('lineItems as accessorial_rate_total', 'accessorial_rate')
            ->withSum('cashVouchers as requested_total', 'amount')
            ->withSum('cvrApprovals as approved_total', 'amount');

        if ($request->boolean('datatable') || $request->ajax()) {
            return $this->datatableResponse($request, clone $baseQuery);
        }

        $summary = $this->buildSummary();

        return view('details.index', compact('summary'));
    }

    protected function datatableResponse(Request $request, $query)
    {
        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $length = $length > 0 ? min($length, 100) : 10;

        $searchValue = trim((string) data_get($request->input('search', []), 'value', ''));

        $recordsTotal = (clone $query)->count();

        if ($searchValue !== '') {
            $query->where(function ($builder) use ($searchValue) {
                $builder
                    ->where('mtm', 'like', '%' . $searchValue . '%')
                    ->orWhere('project_name', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('company', function ($companyQuery) use ($searchValue) {
                        $companyQuery
                            ->where('company_code', 'like', '%' . $searchValue . '%')
                            ->orWhere('company_name', 'like', '%' . $searchValue . '%');
                    })
                    ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
                        $customerQuery->where('name', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        $recordsFiltered = (clone $query)->count();

        $rows = $query
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get();

        $liquidationTotals = $this->loadLiquidationTotals($rows);

        $data = $rows->map(function (DeliveryRequest $dr) use ($liquidationTotals) {
            $totals = $liquidationTotals[$dr->id] ?? ['cash' => 0.0, 'card' => 0.0];

            return [
                'id' => $dr->id,
                'mtm' => $dr->mtm,
                'project_name' => $dr->project_name ?: 'No project name',
                'company_code' => $dr->company->company_code ?? 'N/A',
                'company_name' => $dr->company->company_name ?? 'N/A',
                'customer_name' => $dr->customer->name ?? 'N/A',
                'booking_date' => $dr->booking_date ?: 'N/A',
                'delivery_date' => $dr->delivery_date ?: 'N/A',
                'delivery_rate' => (float) ($dr->delivery_rate ?? 0),
                'accessorial_rate' => (float) ($dr->accessorial_rate_total ?? 0),
                'requested' => (float) ($dr->requested_total ?? 0),
                'approved' => (float) ($dr->approved_total ?? 0),
                'liquidated_cash' => (float) $totals['cash'],
                'liquidated_card' => (float) $totals['card'],
                'line_items' => (int) ($dr->line_items_count ?? 0),
                'cash_vouchers' => (int) ($dr->cash_vouchers_count ?? 0),
                'approvals' => (int) ($dr->cvr_approvals_count ?? 0),
                'liquidations' => (int) ($dr->liquidations_count ?? 0),
            ];
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    protected function buildSummary(): array
    {
        $deliveryRate = (float) DeliveryRequest::query()->sum('delivery_rate');
        $accessorialRate = (float) DeliveryRequestLineItem::query()->sum('accessorial_rate');
        $requested = (float) CashVoucher::query()->sum('amount');
        $approved = (float) \App\Models\cvr_approval::query()->sum('amount');

        $liquidationTotals = $this->sumLiquidations(
            Liquidation::query()->get([
                'allowance',
                'manpower',
                'hauling',
                'right_of_way',
                'roro_expense',
                'cash_charge',
                'gasoline',
                'rfid',
                'others',
            ])
        );

        return [
            'delivery_requests' => DeliveryRequest::query()->count(),
            'delivery_rate' => $deliveryRate,
            'accessorial_rate' => $accessorialRate,
            'requested' => $requested,
            'approved' => $approved,
            'liquidated_cash' => $liquidationTotals['cash'],
            'liquidated_card' => $liquidationTotals['card'],
        ];
    }

    protected function loadLiquidationTotals(Collection $deliveryRequests): array
    {
        $deliveryIds = $deliveryRequests->pluck('id')->filter()->values();

        if ($deliveryIds->isEmpty()) {
            return [];
        }

        $cashVouchers = CashVoucher::query()
            ->whereIn('dr_id', $deliveryIds)
            ->with([
                'cvrApprovals:id,cvr_id',
                'cvrApprovals.liquidations:id,cvr_approval_id,allowance,manpower,hauling,right_of_way,roro_expense,cash_charge,gasoline,rfid,others',
            ])
            ->get(['id', 'dr_id']);

        $totalsByRequest = [];

        foreach ($cashVouchers as $cashVoucher) {
            $totals = $this->sumLiquidations($cashVoucher->cvrApprovals->flatMap->liquidations);
            $current = $totalsByRequest[$cashVoucher->dr_id] ?? ['cash' => 0.0, 'card' => 0.0];

            $totalsByRequest[$cashVoucher->dr_id] = [
                'cash' => $current['cash'] + $totals['cash'],
                'card' => $current['card'] + $totals['card'],
            ];
        }

        return $totalsByRequest;
    }

    protected function sumLiquidations(iterable $liquidations): array
    {
        $totalCash = 0.0;
        $totalCard = 0.0;

        foreach ($liquidations as $liq) {
            $totalCash += (float) ($liq->allowance ?? 0)
                + (float) ($liq->manpower ?? 0)
                + (float) ($liq->hauling ?? 0)
                + (float) ($liq->right_of_way ?? 0)
                + (float) ($liq->roro_expense ?? 0)
                + (float) ($liq->cash_charge ?? 0);

            foreach ((array) ($liq->gasoline ?? []) as $gas) {
                if (($gas['type'] ?? '') === 'cash') {
                    $totalCash += (float) ($gas['amount'] ?? 0);
                } elseif (($gas['type'] ?? '') === 'card') {
                    $totalCard += (float) ($gas['amount'] ?? 0);
                }
            }

            foreach ((array) ($liq->rfid ?? []) as $rfid) {
                if (($rfid['type'] ?? '') === 'cash') {
                    $totalCash += (float) ($rfid['amount'] ?? 0);
                } elseif (($rfid['type'] ?? '') === 'card') {
                    $totalCard += (float) ($rfid['amount'] ?? 0);
                }
            }

            foreach ((array) ($liq->others ?? []) as $other) {
                $totalCash += (float) ($other['amount'] ?? 0);
            }
        }

        return [
            'cash' => $totalCash,
            'card' => $totalCard,
        ];
    }
}
