@extends('admin.layout.app')
@section('title','Dashboard')
@push('css')
    <link rel="stylesheet"
        href="{{ asset('admin/assets/dashboard/css/dataTables.bootstrap4.min.css') }}" />
        <style>
            .table:not(.table-dark):not(.table-light) thead:not(.table-dark) th, .table:not(.table-dark):not(.table-light) tfoot:not(.table-dark) th {
                background-color: #808080bf;
                color:#000 !important;
                border: 1px solid #343a40 !important;
            }
            .table thead th, .table tfoot th{
                font-size: 0.9rem;
                vertical-align: middle;
                text-transform: uppercase !important;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
            }
        
            .table thead {
                /*background-color: #343a40;*/
                color: white;
            }
        
            .table th, .table td {
                text-align: center;
                padding: 10px;
                border: 1px solid #000;
                color: #000;
            }
        
            .table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
        
            .table tbody tr:hover {
                background-color: #e9ecef;
            }
        
            .table th {
                font-size:20px;
                font-weight: bold;
            }
            .table td{
                font-size:18px;
            }
            .table-bordered {
                border: 1px solid #000;
            }
            .client{
                text-transform: uppercase;
            }
            .card-graph{
                background: #c8c9c2;
                height:75%;
            }
            .card-graph-red{
                background: #df720694;
                height:75%;
            }
            .card-graph-green{
                background: #58c38a80;
                height:75%;
            }
            .font-weight-bold{
                font-weight: 600 !important;
            }
            .text-red{
                color:red !important;
            }
            .form-switch .form-check-input{
                    margin-left: 0 !important;
            }
            
        </style>
@endpush
@section('content')
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="content-wrapper container-xxl pt-0 px-0 pb-sm-0 pb-5">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Dashboard Ecommerce Starts -->
            <section id="dashboard-ecommerce">
                <div class="row match-height">
                    <!-- Statistics Card -->
                    @php
                        $users= \App\Models\User::count();
                        $transaction= \App\Models\Transaction::count();
                        $payout= \App\Models\Payout::count();
                        $sub_stores= \App\Models\Client::count();
                    @endphp
                    <div class="col-xl-12 col-md-12 col-12">
                        <div class="row">
                            <div class="@if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Admin") col-md-2 @else col-md-3 @endif">
                                <div class="card bg-primary">
                                    <div class="card-body pb-50">
                                        <h5 class="text-white">Dated: <span class="fw-bolder" style="font-size:20px">{{ now()->format('d-m-Y') }}</span> </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="@if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Admin") col-md-2 @else col-md-3 @endif">
                                <div class="card bg-success">
                                    <div class="card-body pb-50">
                                        <h5 class="text-white">No of Sub Stores: <span class="fw-bolder" style="font-size:20px">{{ $sub_stores }}</span> </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="@if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Admin") col-md-3 @else col-md-3 @endif">
                                <div class="card bg-info">
                                    <div class="card-body pb-50">
                                        <h5 class="text-white">Today Payin No: <span class="fw-bolder" style="font-size:20px">{{ $transaction }}</span> </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="@if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Admin") col-md-2 @else col-md-3 @endif">
                                <div class="card bg-danger">
                                    <div class="card-body pb-50">
                                        <h5 class="text-white">Today Payout No: <span class="fw-bolder" style="font-size:20px">{{ $payout }}</span> </h5>
                                    </div>
                                </div>
                            </div>
                             @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Manager")
                            <div class="col-md-3">
                                <div class="card bg-warning">
                                    <div class="card-body pb-50">
                                        <h5 class="text-black">Monthly EP Payin: <span class="fw-bolder" style="font-size:20px">{{ number_format(round($totalMonthlyAmount,0))}}</span> </h5>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        
                        <div class="row justify-content-center align-items-center mt-1">
                            <div class="col-lg-12 col-12">
                                <div class="card card-company-table">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Manager")
                                                    <tr class="bg-warning">
                                                        <th colspan="@if (auth()->user()->user_role == "Super Admin") 12 @else 5 @endif"  rowspan="2">Surplus Amount Interface</th>
                                                        <th>JC</th>
                                                        <th>EP</th>
                                                        <th colspan="3">Action</th>
                                                    </tr>
                                                    <tr class="bg-warning">
                                                        <th>{{number_format(round($surplusAmount->jazzcash,0))}}</th>
                                                        <th>{{number_format(round($surplusAmount->easypaisa,0))}}</th>
                                                        <th colspan="3"><a data-target="#attributeModal" class="btn btn-primary waves-effect waves-float waves-light open_modal" data-url="{{route('admin.setting.modal_sec')}}">Add Amount</a></th>
                                                    </tr>
                                                    @endif
                                                    <tr>
                                                        <th rowspan="2">
                                                            Client
                                                            @if(auth()->user()->user_role == "Super Admin")
                                                                <div class="dropdown" style="display:inline-block;">
                                                                    <button class="btn btn-light dropdown-toggle" type="button" id="userMultiSelectDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 14px; width: 120px;">
                                                                        Filter Users
                                                                    </button>
                                                                    <ul class="dropdown-menu p-2" aria-labelledby="userMultiSelectDropdown" style="max-height: 300px; overflow-y: auto; min-width: 180px;">
                                                                        <li>
                                                                            <label class="dropdown-item">
                                                                                <input type="checkbox" id="userFilterAll" checked> All
                                                                            </label>
                                                                        </li>
                                                                        @php
                                                                            $userIds = [];
                                                                        @endphp
                                                                        @foreach($data as $item)
                                                                            @php
                                                                                $user = $item['user'];
                                                                            @endphp
                                                                            @if(!in_array($user->id, $userIds))
                                                                                <li>
                                                                                    <label class="dropdown-item">
                                                                                        <input type="checkbox" class="userFilterCheckbox" value="{{ $user->id }}" checked> {{ $user->name }}
                                                                                    </label>
                                                                                </li>
                                                                                @php $userIds[] = $user->id; @endphp
                                                                            @endif
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                        </th>
                                                        @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Client")
                                                        <th rowspan="2">Previous Balance</th>
                                                        <th colspan="4">Payin</th>
                                                        <th colspan="3">Payout</th>
                                                        <th rowspan="2">USDT</th>
                                                        @endif
                                                        <th rowspan="2">Unsettled (Payable)</th>
                                                        <th colspan="3">Wallet</th>
                                                        @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Manager" || auth()->user()->user_role == "Client")
                                                        <th colspan="3" rowspan="3">Balance</th>
                                                        @endif
                                                    </tr>
                                                    <tr>
                                                        @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Client")
                                                        <th>JC</th>
                                                        <th>EP</th>
                                                        <th>Total</th>
                                                        <th>Deduction</th>
                                                        <th>JC</th>
                                                        <th>EP</th>
                                                        <th>Total</th>
                                                        @endif
                                                        <th>JC</th>
                                                        <th>EP</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($data as $item)
                                                        @php
                                                            $user = $item['user'];
                                                        @endphp
                                                    
                                                        @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Manager" || auth()->user()->id == $user->id)
                                                        <tr data-user-id="{{ $user->id }}">
                                                            <td class="client">{{ $user->name }}</td>
                                                    
                                                            @if(auth()->user()->user_role == "Super Admin" || auth()->user()->id == $user->id)
                                                                <td>{{ number_format($item['prev_balance']) }}</td>
                                                                <td class="bg-green">{{ number_format($item['jc_payin']) }}</td>
                                                                <td class="bg-green">{{ number_format($item['ep_payin']) }}</td>
                                                                <td class="bg-green font-weight-bold">{{ number_format($item['total_payin']) }}</td>
                                                                <td class="font-weight-bold text-red">{{ number_format($item['reverse_amount']) }}</td>
                                                                <td class="bg-red">{{ number_format($item['jc_payout']) }}</td>
                                                                <td class="bg-red">{{ number_format($item['ep_payout']) }}</td>
                                                                <td class="bg-red font-weight-bold">{{ number_format($item['total_payout']) }}</td>
                                                                <td>{{ number_format($item['prev_usdt']) }}</td>
                                                            @endif
                                                    
                                                            <td class="font-weight-bold text-red">{{ number_format($item['unsettled_amount']) }}</td>
                                                            <td class="bg-gray">{{ number_format($item['assigned_amount']->jazzcash ?? 0) }}</td>
                                                            <td class="bg-gray">{{ number_format($item['assigned_amount']->easypaisa ?? 0) }}</td>
                                                            <td class="bg-gray font-weight-bold">{{ number_format($item['assigned_amount']->payout_balance ?? 0) }}</td>
                                                    
                                                            @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Manager" || auth()->user()->id == $user->id)
                                                            <td class="bg-warning">{{ number_format(round($item['unsettled_amount_balance'], 0)) }}</td>
                                                            @endif
                                                            @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Manager")
                                                            <td class="bg-warning">
                                                                <div class="d-flex justify-content-start">
                                                                    @if($item['setting']->auto == 0)
                                                                        <a class="dropdown-item btn btn-primary w-auto open_modal me-1" 
                                                                           data-url="{{ route('admin.setting.modal') }}" 
                                                                           data-id="{{ $user->id }}">
                                                                            <i class="fa fa-edit"></i>
                                                                        </a>
                                                                    @else
                                                                        <a class="dropdown-item btn btn-success w-auto open_modal me-1" 
                                                                           data-url="{{ route('admin.setting.third_modal') }}" 
                                                                           data-id="{{ $user->id }}">
                                                                            <i class="fa fa-edit"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="bg-warning">
                                                                <div class="form-check form-switch">
                                                                    <input 
                                                                        class="form-check-input toggle-switch" 
                                                                        type="checkbox" 
                                                                        data-id="{{ $user->id }}"
                                                                        data-type="auto"
                                                                        @if($item['setting']->auto == 1) checked @endif>
                                                                </div>
                                                            </td>
                                                            @endif
                                                        </tr>
                                                        @endif
                                                    @endforeach
                                                    
                                                    @if(auth()->user()->user_role == "Super Admin" || auth()->user()->user_role == "Manager")
                                                        <tr>
                                                            <td class="client font-weight-bold">Total</td>
                                                        
                                                            @if(auth()->user()->user_role == "Super Admin")
                                                                <td class="font-weight-bold">{{ number_format($totals['prev_balance']) }}</td>
                                                                <td class="bg-green font-weight-bold">{{ number_format($totals['jc_payin']) }}</td>
                                                                <td class="bg-green font-weight-bold">{{ number_format($totals['ep_payin']) }}</td>
                                                                <td class="bg-green font-weight-bold">{{ number_format($totals['total_payin']) }}</td>
                                                                <td class="bg-green font-weight-bold">{{ number_format($totals['reverse_amount']) }}</td>
                                                                <td class="bg-red font-weight-bold">{{ number_format($totals['jc_payout']) }}</td>
                                                                <td class="bg-red font-weight-bold">{{ number_format($totals['ep_payout']) }}</td>
                                                                <td class="bg-red font-weight-bold">{{ number_format($totals['total_payout']) }}</td>
                                                                <td class="font-weight-bold">{{ number_format($totals['prev_usdt']) }}</td>
                                                            @endif
                                                        
                                                            <td class="font-weight-bold text-red">{{ number_format($totals['unsettled_amount']) }}</td>
                                                            <td class="bg-gray font-weight-bold">{{ number_format($totals['assigned_jc']) }}</td>
                                                            <td class="bg-gray font-weight-bold">{{ number_format($totals['assigned_ep']) }}</td>
                                                            <td class="bg-gray font-weight-bold">{{ number_format($totals['assigned_payout']) }}</td>
                                                            <td colspan="3" class="bg-warning font-weight-bold">{{ number_format($totals['unsettled_amount_balance']) }}</td>
                                                        </tr>
                                                    @endif

                                                </tbody>
                                                {{--<thead class="border">
                                                    <tr class="text-center">
                                                        <th>Date</th>
                                                        <th>Opening Bal</th>
                                                        <th>EP Payin</th>
                                                        <th>Complaint Deduction</th>
                                                        <th>Transfered to Wallet</th>
                                                        <th>USDT</th>
                                                        <th>Closing Bal/Unsettled</th>
                                                        @if(auth()->user()->user_role == "Super Admin")
                                                            <th rowspan="2">Action</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody class="border">
                                                    <tr class="text-center">
                                                        <td>{{ $results->date->format('d-M') }}</td>
                                                        <td>{{ number_format(round($results->opening_bal,0))}}</td>
                                                        <td>{{ number_format(round($results->ep_payin,0)) }}</td>
                                                        <td>{{ number_format(round($results->reverse_amount)) }}</td>
                                                        <td>{{ number_format(round($results->ep_payout,0)) }}</td>
                                                        <td>{{ number_format(round($results->usdt,0)) }}</td>
                                                        <td class="font-weight-bold text-red">{{ number_format($results->closing_bal) }}</td>
                                                        @if(auth()->user()->user_role == "Super Admin")
                                                            <td>
                                                                <a data-target="#attributeModal"
                                                                   class="btn btn-primary waves-effect waves-float waves-light open_modal" 
                                                                   data-url="{{route('admin.settlement.modal',$results->id)}}">
                                                                    Manual
                                                                </a>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                </tbody>--}}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @if(count($userStats) > 0)
                    <div class="row mt-1">
                        @foreach($userStats as $userId => $stats)
                            <div class="col-md-4">
                                <div class="card shadow-lg card-graph">
                                    <div class="card-body text-center">
                                        <h5 class="card-title font-weight-bold">{{ $stats['name'] }} Success Rate</h5>
                                        <div id="successRateChart{{ $userId }}"></div>
                                    </div>
                                </div>
                            </div>
                
                            <div class="col-md-4">
                                <div class="card card-graph-red">
                                    <div class="card-header text-center">
                                        <h5 class="card-title font-weight-bold">{{ $stats['name'] }} JazzCash Pending</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="jcChart{{ $userId }}"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card card-graph-green">
                                    <div class="card-header text-center">
                                        <h5 class="card-title font-weight-bold">{{ $stats['name'] }} Easypaisa Pending</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="epChart{{ $userId }}"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </section>
        </div>
    </div>
</div>
@endsection
@push('js')

<script>
document.addEventListener("DOMContentLoaded", function () {
    const userStats = @json($userStats);

    for (const [userId, data] of Object.entries(userStats)) {
        createSemiCircleChart("successRateChart" + userId, data.success_rate, "#FF4500");
        renderChart("#jcChart" + userId, data.jazzcash_pending, "JC Pending", "#FF5733");
        renderChart("#epChart" + userId, data.easypaisa_pending, "EP Pending", "#28C76F");
    }

    function createSemiCircleChart(containerId, value, color) {
        const options = {
            series: [value],
            chart: { type: 'radialBar', height: 160 },
            plotOptions: {
                radialBar: {
                    hollow: { size: '60%' },
                    startAngle: -90,
                    endAngle: 90,
                    track: { background: "#e0e0e0" },
                    dataLabels: {
                        name: { show: true, fontSize: '14px', color: "#999", offsetY: -10 },
                        value: { fontSize: '22px', color: "#333", fontWeight: 'bold', offsetY: 10, formatter: val => val + "%" }
                    }
                }
            },
            colors: [color]
        };
        new ApexCharts(document.querySelector("#" + containerId), options).render();
    }

    function renderChart(selector, value, label, color) {
        const options = {
            chart: { type: "radialBar", height: 150 },
            series: [value],
            labels: [label],
            colors: [color],
            plotOptions: {
                radialBar: {
                    hollow: { size: "70%" },
                    dataLabels: {
                        show: true,
                        value: {
                            fontSize: "20px",
                            fontWeight: "bold",
                            formatter: val => val
                        }
                    }
                }
            }
        };
        new ApexCharts(document.querySelector(selector), options).render();
    }
});
</script>

<script>
$(document).ready(function () {
    $('.toggle-switch').on('change', function () {
        const isChecked = $(this).is(':checked'); 
        const id = $(this).data('id');
        const type = $(this).data('type');
        // Send AJAX request to update backend
        updateToggleStatus(id, type, isChecked);
    });

    function updateToggleStatus(id, type, status) {
        $.ajax({
            url: '{{ route("admin.setting.api.suspend") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            contentType: 'application/json',
            data: JSON.stringify({ id: id, type: type, status: status ? 1 : 0 }),
            success: function (response) {
                location.reload();
                console.log('Toggle updated:', response);
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
            },
        });
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Multi-select user filter logic
    const allCheckbox = document.getElementById('userFilterAll');
    const userCheckboxes = document.querySelectorAll('.userFilterCheckbox');

    function updateTableVisibility() {
        const checkedUserIds = Array.from(userCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        if (allCheckbox.checked || checkedUserIds.length === userCheckboxes.length) {
            // Show all rows
            document.querySelectorAll('tbody tr[data-user-id]').forEach(row => {
                row.style.display = '';
            });
            allCheckbox.checked = true;
            userCheckboxes.forEach(cb => cb.checked = true);
        } else {
            document.querySelectorAll('tbody tr[data-user-id]').forEach(row => {
                if (checkedUserIds.includes(row.getAttribute('data-user-id'))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            allCheckbox.checked = false;
        }
    }

    allCheckbox.addEventListener('change', function () {
        if (this.checked) {
            userCheckboxes.forEach(cb => cb.checked = true);
        } else {
            userCheckboxes.forEach(cb => cb.checked = false);
        }
        updateTableVisibility();
    });

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const checkedCount = Array.from(userCheckboxes).filter(cb => cb.checked).length;
            if (checkedCount === userCheckboxes.length) {
                allCheckbox.checked = true;
            } else {
                allCheckbox.checked = false;
            }
            updateTableVisibility();
        });
    });

    // Initial state
    updateTableVisibility();
});
</script>
@endpush