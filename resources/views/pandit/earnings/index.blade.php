@extends('layouts.pandit-dashboard')

@section('title', 'Earnings - BhaktiDeep')

@php
    $activeMenu = 'earnings';
@endphp

@section('content')
<style>
/* =========================================================
   PANDIT EARNINGS PAGE ONLY
   ========================================================= */

.pandit-earnings-stats {
    margin-bottom: 24px;
}

/* Summary cards */
.pandit-earnings-stats .pandit-stat-card {
    min-height: 145px;
}

.pandit-earnings-stats .pandit-stat-card > i {
    font-size: 22px;
}

.pandit-earnings-stats .pandit-stat-card span {
    display: block;
    margin-top: 12px;
    font-size: 13px;
    color: #8b6f5c;
}

.pandit-earnings-stats .pandit-stat-card strong {
    display: block;
    margin-top: 5px;
    font-size: 23px;
    line-height: 1.2;
    color: #3f2417;
}


/* =========================================================
   FILTER PANEL
   ========================================================= */

.pandit-earnings-filter-panel {
    margin-bottom: 24px;
}

.pandit-earnings-filter-form {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 14px;
    align-items: end;
}

.pandit-earnings-filter-form label {
    display: flex;
    flex-direction: column;
    gap: 7px;
    margin: 0;

    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;

    color: #7b604f;
}

.pandit-earnings-filter-form select,
.pandit-earnings-filter-form input {
    width: 100%;
    min-height: 42px;

    padding: 9px 11px;

    border: 1px solid rgba(232, 91, 33, .22);
    border-radius: 10px;

    background: #fffaf0;
    color: #3f2417;

    font-size: 13px;
    font-weight: 500;

    outline: none;
    box-shadow: none;

    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}

.pandit-earnings-filter-form select:focus,
.pandit-earnings-filter-form input:focus {
    border-color: #e85b21;
    box-shadow: 0 0 0 3px rgba(232, 91, 33, .08);
}

.pandit-earnings-filter-actions {
    grid-column: 1 / -1;

    display: flex;
    justify-content: flex-end;
    align-items: center;
    flex-wrap: wrap;

    gap: 10px;
    margin-top: 3px;
}

.pandit-earnings-filter-button,
.pandit-earnings-reset-button {
    min-height: 40px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 9px 16px;

    border-radius: 999px;

    font-size: 12px;
    font-weight: 700;

    text-decoration: none;

    transition:
        background .2s ease,
        color .2s ease,
        border-color .2s ease,
        transform .2s ease;
}

.pandit-earnings-filter-button {
    border: 1px solid #e85b21;

    background: #e85b21;
    color: #fff;
}

.pandit-earnings-filter-button:hover {
    background: #c94818;
    border-color: #c94818;
}

.pandit-earnings-reset-button {
    border: 1px solid rgba(232, 91, 33, .32);

    background: transparent;
    color: #c94e1d;
}

.pandit-earnings-reset-button:hover {
    background: rgba(232, 91, 33, .07);
    color: #a43b14;
}


/* =========================================================
   EARNING HISTORY PANEL
   ========================================================= */

.pandit-earnings-panel {
    margin-bottom: 25px;
}

.pandit-earnings-pending-total {
    display: flex;
    flex-direction: column;
    align-items: flex-end;

    gap: 2px;
}

.pandit-earnings-pending-total span {
    font-size: 11px;
    color: #8b6f5c;
}

.pandit-earnings-pending-total strong {
    font-size: 17px;
    color: #3f2417;
}


/* =========================================================
   TABLE
   ========================================================= */

.pandit-earnings-table-wrap {
    width: 100%;
    overflow-x: auto;

    border: 1px solid rgba(232, 91, 33, .13);
    border-radius: 14px;

    background: #fffdf7;
}

.pandit-earnings-table {
    width: 100%;
    min-width: 1180px;

    border-collapse: collapse;
}

.pandit-earnings-table thead {
    background: #fff7e8;
}

.pandit-earnings-table th {
    padding: 12px 12px;

    text-align: left;

    font-size: 10px;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: .035em;

    color: #8a6652;

    border-bottom: 1px solid rgba(232, 91, 33, .15);

    white-space: nowrap;
}

.pandit-earnings-table td {
    padding: 14px 12px;

    vertical-align: middle;

    border-bottom: 1px solid rgba(232, 91, 33, .10);

    font-size: 12px;
    color: #3f2417;
}

.pandit-earnings-table tbody tr:last-child td {
    border-bottom: 0;
}

.pandit-earnings-table tbody tr {
    transition: background .2s ease;
}

.pandit-earnings-table tbody tr:hover {
    background: #fffaf1;
}

.pandit-earnings-table td strong {
    display: block;

    font-size: 12px;
    font-weight: 700;

    color: #3f2417;
}

.pandit-earnings-table td small {
    display: block;

    margin-top: 4px;

    font-size: 10px;
    line-height: 1.4;

    color: #907563;
}


/* =========================================================
   MONEY
   ========================================================= */

.pandit-money {
    white-space: nowrap;

    font-weight: 600;
}

.pandit-money-total {
    color: #c94e1d !important;
    font-weight: 800 !important;
}


/* =========================================================
   QUICK DAKSHINA CHIP
   ========================================================= */

.pandit-earning-type-chip {
    width: fit-content;

    display: inline-flex;
    align-items: center;

    margin-top: 5px;
    padding: 3px 8px;

    border-radius: 999px;

    background: #fff0d8;
    color: #b85a1b;

    font-size: 9px;
    font-weight: 700;
}


/* =========================================================
   STATUS
   ========================================================= */

.pandit-earning-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 25px;

    padding: 4px 9px;

    border-radius: 999px;

    font-size: 9px;
    font-weight: 700;

    white-space: nowrap;

    background: #f4eee7;
    color: #725747;
}


/* Paid / Completed / Ready */
.pandit-earning-status.status-paid,
.pandit-earning-status.status-completed,
.pandit-earning-status.status-ready {
    background: #e6f6e9;
    color: #23783b;
}


/* Confirmed / Processing */
.pandit-earning-status.status-confirmed,
.pandit-earning-status.status-processing {
    background: #eaf3ff;
    color: #326da8;
}


/* Pending / Hold */
.pandit-earning-status.status-pending,
.pandit-earning-status.status-hold {
    background: #fff2d8;
    color: #a66710;
}


/* Cancelled / Failed */
.pandit-earning-status.status-cancelled,
.pandit-earning-status.status-cancelled_by_pandit,
.pandit-earning-status.status-failed {
    background: #ffe8e3;
    color: #bf4029;
}


/* Not available */
.pandit-earning-status.status-not_available {
    background: #f1f1f1;
    color: #777;
}


/* Cancellation reason */
.pandit-earning-cancel-reason {
    max-width: 150px;

    margin-top: 6px !important;

    color: #b7492c !important;

    line-height: 1.35;
}


/* =========================================================
   VIEW BOOKING BUTTON
   ========================================================= */

.pandit-earning-view-link {
    width: 32px;
    height: 32px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid rgba(232, 91, 33, .25);
    border-radius: 50%;

    background: #fffaf0;

    color: #d65420;

    text-decoration: none;

    transition:
        background .2s ease,
        color .2s ease,
        border-color .2s ease;
}

.pandit-earning-view-link:hover {
    background: #e85b21;
    border-color: #e85b21;

    color: #fff;
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.pandit-earnings-empty {
    min-height: 180px;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    text-align: center;

    padding: 30px;
}

.pandit-earnings-empty i {
    margin-bottom: 10px;

    font-size: 31px;
    color: #dc6a25;
}

.pandit-earnings-empty strong {
    font-size: 15px !important;
}

.pandit-earnings-empty span {
    margin-top: 5px;

    font-size: 11px;
    color: #8a6d5a;
}


/* =========================================================
   PAGINATION
   ========================================================= */

.pandit-earnings-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;

    margin-top: 18px;
}

.pandit-earnings-pagination a,
.pandit-earnings-pagination span {
    min-height: 37px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 6px;

    padding: 7px 14px;

    border: 1px solid rgba(232, 91, 33, .23);
    border-radius: 999px;

    background: #fffaf0;

    color: #c94e1d;

    font-size: 11px;
    font-weight: 700;

    text-decoration: none;
}

.pandit-earnings-pagination a:hover {
    background: #e85b21;
    border-color: #e85b21;

    color: #fff;
}

.pandit-earnings-pagination span.disabled {
    opacity: .45;
    cursor: not-allowed;
}

.pandit-earnings-pagination strong {
    font-size: 11px;
    color: #765947;
}


/* =========================================================
   TABLE SCROLLBAR
   ========================================================= */

.pandit-earnings-table-wrap::-webkit-scrollbar {
    height: 7px;
}

.pandit-earnings-table-wrap::-webkit-scrollbar-track {
    background: #fff4df;
}

.pandit-earnings-table-wrap::-webkit-scrollbar-thumb {
    background: rgba(232, 91, 33, .30);
    border-radius: 20px;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1200px) {

    .pandit-earnings-filter-form {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

}


@media (max-width: 768px) {

    .pandit-earnings-filter-form {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .pandit-earnings-pending-total {
        align-items: flex-start;
        margin-top: 10px;
    }

    .pandit-earnings-pagination {
        flex-wrap: wrap;
        justify-content: center;
    }

    .pandit-earnings-pagination strong {
        width: 100%;
        order: -1;
        text-align: center;
    }

}


@media (max-width: 520px) {

    .pandit-earnings-filter-form {
        grid-template-columns: 1fr;
    }

    .pandit-earnings-filter-actions {
        justify-content: stretch;
    }

    .pandit-earnings-filter-button,
    .pandit-earnings-reset-button {
        flex: 1;
    }

    .pandit-earnings-table th {
        padding: 10px;
    }

    .pandit-earnings-table td {
        padding: 12px 10px;
    }

    .pandit-earnings-pagination a,
    .pandit-earnings-pagination span {
        flex: 1;
    }

}
</style>

<div class="pandit-page-heading">

    <div>
        <p>Pandit Earnings</p>
        <h1>Earnings</h1>
    </div>

    <a
        href="{{ route('pandit.dashboard') }}"
        class="pandit-add-btn"
    >
        <i class="bi bi-grid-1x2-fill"></i>
        Dashboard
    </a>

</div>


<section class="pandit-stats-grid pandit-earnings-stats">

    <article class="pandit-stat-card">
        <i class="bi bi-wallet2"></i>

        <span>
            Total Earnings
        </span>

        <strong>
            ₹{{ number_format($summary['total'], 2) }}
        </strong>
    </article>


    <article class="pandit-stat-card">

        <i class="bi bi-stars"></i>

        <span>
            Service Earnings
        </span>

        <strong>
            ₹{{ number_format($summary['service'], 2) }}
        </strong>

    </article>


    <article class="pandit-stat-card">

        <i class="bi bi-gift"></i>

        <span>
            Dakshina
        </span>

        <strong>
            ₹{{ number_format($summary['dakshina'], 2) }}
        </strong>

    </article>


    <article class="pandit-stat-card">

        <i class="bi bi-bank"></i>

        <span>
            Paid to Bank
        </span>

        <strong>
            ₹{{ number_format($summary['paid'], 2) }}
        </strong>

    </article>

</section>


<section class="pandit-panel pandit-earnings-filter-panel">

    <div class="pandit-panel-heading">

        <div>
            <h2>Filter Earnings</h2>

            <p>
                Check today, yesterday,
                custom dates, service or payout status.
            </p>
        </div>

        <span>
            <i class="bi bi-funnel"></i>
        </span>

    </div>


    <form
        method="GET"
        action="{{ route('pandit.earnings') }}"
        class="pandit-earnings-filter-form"
    >

        <label>

            Date

            <select name="date_filter">

                <option
                    value="all"
                    @selected($filters['date_filter'] === 'all')
                >
                    All Dates
                </option>

                <option
                    value="today"
                    @selected($filters['date_filter'] === 'today')
                >
                    Today
                </option>

                <option
                    value="yesterday"
                    @selected($filters['date_filter'] === 'yesterday')
                >
                    Yesterday
                </option>

                <option
                    value="last_7_days"
                    @selected($filters['date_filter'] === 'last_7_days')
                >
                    Last 7 Days
                </option>

                <option
                    value="last_30_days"
                    @selected($filters['date_filter'] === 'last_30_days')
                >
                    Last 30 Days
                </option>

                <option
                    value="custom"
                    @selected($filters['date_filter'] === 'custom')
                >
                    Custom Dates
                </option>

            </select>

        </label>


        <label>

            From Date

            <input
                type="date"
                name="from_date"
                value="{{ $filters['from_date'] }}"
            >

        </label>


        <label>

            To Date

            <input
                type="date"
                name="to_date"
                value="{{ $filters['to_date'] }}"
            >

        </label>


        <label>

            Service

            <select name="service_type">

                <option
                    value="all"
                    @selected($filters['service_type'] === 'all')
                >
                    All Services
                </option>

                <option
                    value="hawan"
                    @selected($filters['service_type'] === 'hawan')
                >
                    Hawan
                </option>

                <option
                    value="pooja"
                    @selected($filters['service_type'] === 'pooja')
                >
                    Pooja
                </option>

            </select>

        </label>


        <label>

            Payout Status

            <select name="status">

                <option value="all"
                    @selected($filters['status'] === 'all')
                >
                    All Statuses
                </option>

                <option value="hold"
                    @selected($filters['status'] === 'hold')
                >
                    On Hold
                </option>

                <option value="ready"
                    @selected($filters['status'] === 'ready')
                >
                    Ready
                </option>

                <option value="processing"
                    @selected($filters['status'] === 'processing')
                >
                    Processing
                </option>

                <option value="paid"
                    @selected($filters['status'] === 'paid')
                >
                    Paid
                </option>

                <option value="cancelled"
                    @selected($filters['status'] === 'cancelled')
                >
                    Cancelled
                </option>

                <option value="failed"
                    @selected($filters['status'] === 'failed')
                >
                    Failed
                </option>

            </select>

        </label>


        <label>

            Per Page

            <select name="per_page">

                <option
                    value="10"
                    @selected((int) $filters['per_page'] === 10)
                >
                    10
                </option>

                <option
                    value="15"
                    @selected((int) $filters['per_page'] === 15)
                >
                    15
                </option>

            </select>

        </label>


        <div class="pandit-earnings-filter-actions">

            <button
                type="submit"
                class="pandit-earnings-filter-button"
            >
                <i class="bi bi-search"></i>
                Apply Filters
            </button>


            <a
                href="{{ route('pandit.earnings') }}"
                class="pandit-earnings-reset-button"
            >
                <i class="bi bi-arrow-counterclockwise"></i>
                Reset
            </a>

        </div>

    </form>

</section>


<section class="pandit-panel pandit-earnings-panel">

    <div class="pandit-panel-heading">

        <div>

            <h2>
                Earning History
            </h2>

            <p>

                @if($payouts->total())

                    Showing
                    {{ $payouts->firstItem() }}
                    –
                    {{ $payouts->lastItem() }}

                    of

                    {{ $payouts->total() }}
                    records

                @else

                    No earning records found

                @endif

            </p>

        </div>


        <div class="pandit-earnings-pending-total">

            <span>
                Pending / Ready
            </span>

            <strong>
                ₹{{ number_format($summary['pending'], 2) }}
            </strong>

        </div>

    </div>


    <div class="pandit-earnings-table-wrap">

        <table class="pandit-earnings-table">

            <thead>

                <tr>

                    <th>Booking</th>

                    <th>Date</th>

                    <th>
                        Service / Yajman
                    </th>

                    <th>
                        Booking
                    </th>

                    <th>
                        User Payment
                    </th>

                    <th>
                        Service Earning
                    </th>

                    <th>
                        Dakshina
                    </th>

                    <th>
                        Total
                    </th>

                    <th>
                        Payout
                    </th>

                    <th></th>

                </tr>

            </thead>


            <tbody>

            @forelse($payouts as $row)

                @php

                    $bookingStatus =
                        str_replace(
                            '_',
                            ' ',
                            $row['booking_status']
                        );

                    $paymentStatus =
                        str_replace(
                            '_',
                            ' ',
                            $row['payment_status']
                        );

                    $payoutStatus =
                        str_replace(
                            '_',
                            ' ',
                            $row['payout_status']
                        );

                @endphp


                <tr>

                    <td>

                        <strong>
                            {{ $row['booking_id'] }}
                        </strong>

                        <small>
                            {{ ucfirst($row['type'] ?? 'Dakshina') }}
                        </small>

                    </td>


                    <td>

                        <strong>
                            {{ $row['booking_date']?->format('d M Y') ?? '—' }}
                        </strong>

                        @if($row['paid_at'])

                            <small>
                                Paid
                                {{ $row['paid_at']->format('d M Y') }}
                            </small>

                        @endif

                    </td>


                    <td>

                        <strong>
                            {{ $row['service_name'] }}
                        </strong>

                        <small>
                            {{ $row['yajman'] }}
                        </small>


                        @if(
                            $row['payout_type']
                            ===
                            \App\Models\PanditPayout::TYPE_QUICK_DAKSHINA
                        )

                            <span class="pandit-earning-type-chip">
                                Quick Dakshina
                            </span>

                        @endif

                    </td>


                    <td>

                        <span
                            class="
                                pandit-earning-status
                                status-{{ $row['booking_status'] }}
                            "
                        >

                            {{ ucwords($bookingStatus) }}

                        </span>


                        @if($row['cancel_reason'])

                            <small
                                class="pandit-earning-cancel-reason"
                                title="{{ $row['cancel_reason'] }}"
                            >

                                {{
                                    \Illuminate\Support\Str::limit(
                                        $row['cancel_reason'],
                                        42
                                    )
                                }}

                            </small>

                        @endif

                    </td>


                    <td>

                        <span
                            class="
                                pandit-earning-status
                                status-{{ $row['payment_status'] }}
                            "
                        >

                            {{ ucwords($paymentStatus) }}

                        </span>

                    </td>


                    <td class="pandit-money">

                        ₹{{
                            number_format(
                                $row['service_earning'],
                                2
                            )
                        }}

                    </td>


                    <td class="pandit-money">

                        ₹{{
                            number_format(
                                $row['dakshina'],
                                2
                            )
                        }}

                    </td>


                    <td
                        class="
                            pandit-money
                            pandit-money-total
                        "
                    >

                        ₹{{
                            number_format(
                                $row['total'],
                                2
                            )
                        }}

                    </td>


                    <td>

                        <span
                            class="
                                pandit-earning-status
                                status-{{ $row['payout_status'] }}
                            "
                        >

                            {{
                                ucwords(
                                    $payoutStatus === 'hold'
                                        ? 'On Hold'
                                        : $payoutStatus
                                )
                            }}

                        </span>

                    </td>


                    <td>

                        @if($row['detail_url'])

                            <a
                                href="{{ $row['detail_url'] }}"
                                class="pandit-earning-view-link"
                                aria-label="View {{ $row['booking_id'] }}"
                            >
                                <i class="bi bi-arrow-up-right"></i>
                            </a>

                        @endif

                    </td>

                </tr>


            @empty

                <tr>

                    <td colspan="10">

                        <div class="pandit-earnings-empty">

                            <i class="bi bi-wallet2"></i>

                            <strong>
                                No earnings found
                            </strong>

                            <span>
                                Try changing the selected filters.
                            </span>

                        </div>

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>


    @if($payouts->hasPages())

        <div class="pandit-earnings-pagination">

            @if($payouts->onFirstPage())

                <span class="disabled">
                    <i class="bi bi-chevron-left"></i>
                    Previous
                </span>

            @else

                <a href="{{ $payouts->previousPageUrl() }}">
                    <i class="bi bi-chevron-left"></i>
                    Previous
                </a>

            @endif


            <strong>

                Page
                {{ $payouts->currentPage() }}

                of

                {{ $payouts->lastPage() }}

            </strong>


            @if($payouts->hasMorePages())

                <a href="{{ $payouts->nextPageUrl() }}">
                    Next
                    <i class="bi bi-chevron-right"></i>
                </a>

            @else

                <span class="disabled">
                    Next
                    <i class="bi bi-chevron-right"></i>
                </span>

            @endif

        </div>

    @endif

</section>

@endsection