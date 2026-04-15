@extends('admin.layout.app')
@section('title','Api Setting')
@push('css')
<link rel="stylesheet" href="{{ asset('admin/assets/dashboard/css/dataTables.bootstrap4.min.css') }}" />
<style>
    .toggle-switch{
        margin-left: 10px;
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
            <section id="row-grouping-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between">
                                <h4 class="card-title text-capitalize">Test Payin</h4>
                            </div>
                            <div class="card-body p-1">
                                <form action="{{route('admin.testing.payin')}}" method="post">
                                    @csrf
                                    @php
                                        $orderId = 'Khushi-' . now()->format('YmdHis') . '-' . rand(1000, 9999);
                                    @endphp

                                    <input type="hidden" name="orderId" value="{{ $orderId }}">
                                    <input type="hidden" name="email" value="testing@khushipay.com">
                                    <input type="hidden" name="client_email" value="testing@khushipay.com">
                                    <input type="hidden" name="callback_url" value="www.example-testing.com">
                                    <div class="row mt-1 mb-1">
                                        <div class="form-group col-md-3">
                                            <label>Payment Method</label>
                                            <select name="payment_method" id="payment_method" class="form-control" required>
                                                <option value="" disabled selected>Select One ..</option>
                                                <option value="easypaisa">Easypaisa</option>
                                                <option value="jazzcash">Jazzcash</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Phone No</label>
                                            <input class="form-control" name="phone" placeholder="03XXXXXXXXX" type="input" required>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Amount</label>
                                            <input class="form-control" name="amount" type="number" min="1" required>
                                        </div>
                                        <div class="form-group col-md-3 d-flex align-items-end">
                                            <button class="btn btn-primary" type="submit">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between">
                                <h4 class="card-title text-capitalize">Api Setting</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="material-datatables">
                                    <table class="table table-hover m-b-0 datatables" cellspacing="0" width="100%" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Client Name</th>
                                                <th>Payin Jazzcash</th>
                                                <th>Payin Easypaisa</th>
                                                <th>Payout Jazzcash</th>
                                                <th>Payout Easypaisa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($list as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input 
                                                            class="form-check-input toggle-switch" 
                                                            type="checkbox" 
                                                            data-id="{{ $item->id }}"
                                                            data-type="jc_api"
                                                            @if($item->jc_api == 1) checked @endif
                                                        >
                                                        <label class="form-check-label">
                                                            <span class="status-label">
                                                                {{ $item->jc_api == 1 ? 'ON' : 'OFF' }}
                                                            </span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input 
                                                            class="form-check-input toggle-switch" 
                                                            type="checkbox" 
                                                            data-id="{{ $item->id }}" 
                                                            data-type="ep_api"
                                                            @if($item->ep_api == 1) checked @endif
                                                        >
                                                        <label class="form-check-label">
                                                            <span class="status-label">
                                                                {{ $item->ep_api == 1 ? 'ON' : 'OFF' }}
                                                            </span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input 
                                                            class="form-check-input toggle-switch" 
                                                            type="checkbox" 
                                                            data-id="{{ $item->id }}"
                                                            data-type="payout_jc_api"
                                                            @if($item->payout_jc_api == 1) checked @endif
                                                        >
                                                        <label class="form-check-label">
                                                            <span class="status-label">
                                                                {{ $item->payout_jc_api == 1 ? 'ON' : 'OFF' }}
                                                            </span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input 
                                                            class="form-check-input toggle-switch" 
                                                            type="checkbox" 
                                                            data-id="{{ $item->id }}" 
                                                            data-type="payout_ep_api"
                                                            @if($item->payout_ep_api == 1) checked @endif
                                                        >
                                                        <label class="form-check-label">
                                                            <span class="status-label">
                                                                {{ $item->payout_ep_api == 1 ? 'ON' : 'OFF' }}
                                                            </span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between">
                                <h4 class="card-title text-capitalize">New User Verification</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="material-datatables">
                                    <table class="table table-hover m-b-0" cellspacing="0" width="100%" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Client Name</th>
                                                <th>New User Verification</th>
                                                <th>Max Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($list as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item->name ?: $item->email }}</td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input
                                                                class="form-check-input new-user-verification-toggle"
                                                                type="checkbox"
                                                                data-user-id="{{ $item->id }}"
                                                                @if($item->new_user_verification == 1) checked @endif
                                                            >
                                                            <label class="form-check-label">
                                                                <span class="status-label">
                                                                    {{ $item->new_user_verification == 1 ? 'ON' : 'OFF' }}
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td style="max-width: 180px;">
                                                        <input
                                                            type="number"
                                                            min="1"
                                                            step="1"
                                                            class="form-control new-user-max-amount"
                                                            data-user-id="{{ $item->id }}"
                                                            value="{{ (int) ($item->new_user_max_amount ?? 0) }}"
                                                            placeholder="Enter max amount"
                                                        >
                                                    </td>
                                                    <td>
                                                        <button
                                                            type="button"
                                                            class="btn btn-primary btn-sm save-new-user-verification"
                                                            data-user-id="{{ $item->id }}"
                                                        >
                                                            Save
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between">
                                <h4 class="card-title text-capitalize">Jazzcash Schedule Setting</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="material-datatables">
                                    <table class="table table-hover m-b-0 datatables" cellspacing="0" width="100%" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Type</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($list2 as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->type }}</td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input 
                                                            class="form-check-input toggle-switch-jc" 
                                                            type="checkbox" 
                                                            data-id="{{ $item->id }}" 
                                                            @if($item->value == 1) checked @endif
                                                        >
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between">
                                <h4 class="card-title text-capitalize">Easypaisa Schedule Setting</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="material-datatables">
                                    <table class="table table-hover m-b-0 datatables" cellspacing="0" width="100%" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Type</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($list3 as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->type }}</td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input 
                                                            class="form-check-input toggle-switch-ep" 
                                                            type="checkbox" 
                                                            data-id="{{ $item->id }}" 
                                                            @if($item->value == 1) checked @endif
                                                        >
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@endsection
@push('js')
<script>
$(document).ready(function () {
    $('.toggle-switch').on('change', function () {
        const isChecked = $(this).is(':checked'); 
        const id = $(this).data('id');
        const type = $(this).data('type');
        const statusLabel = $(this).siblings('.form-check-label').find('.status-label');

        // Update label text
        statusLabel.text(isChecked ? 'ON' : 'OFF');

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
$(document).ready(function () {
    $('.new-user-verification-toggle').on('change', function () {
        const toggle = $(this);
        const statusLabel = toggle.closest('.form-check').find('.status-label');
        statusLabel.text(toggle.is(':checked') ? 'ON' : 'OFF');
    });

    $('.save-new-user-verification').on('click', function () {
        const button = $(this);
        const userId = button.data('user-id');
        const toggle = $('.new-user-verification-toggle[data-user-id="' + userId + '"]');
        const maxAmountInput = $('.new-user-max-amount[data-user-id="' + userId + '"]');
        const statusLabel = toggle.closest('.form-check').find('.status-label');
        const isChecked = toggle.is(':checked');
        const maxAmountValue = maxAmountInput.val();

        if (!/^\d+$/.test(String(maxAmountValue)) || parseInt(maxAmountValue, 10) <= 0) {
            toastr.error('Max amount must be an integer greater than 0.');
            maxAmountInput.focus();
            return;
        }

        button.prop('disabled', true);
        toggle.prop('disabled', true);
        maxAmountInput.prop('disabled', true);

        $.ajax({
            url: '{{ route("admin.user.toggle_verification") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            contentType: 'application/json',
            data: JSON.stringify({
                user_id: userId,
                status: isChecked ? 1 : 0,
                new_user_max_amount: parseInt(maxAmountValue, 10)
            }),
            success: function (response) {
                toastr.success(response.message || 'Updated successfully.');
            },
            error: function (xhr) {
                statusLabel.text(toggle.is(':checked') ? 'ON' : 'OFF');
                const message = xhr.responseJSON?.message || xhr.responseJSON?.errors?.new_user_max_amount?.[0] || 'Unable to update New User Verification.';
                toastr.error(message);
            },
            complete: function () {
                statusLabel.text(toggle.is(':checked') ? 'ON' : 'OFF');
                button.prop('disabled', false);
                toggle.prop('disabled', false);
                maxAmountInput.prop('disabled', false);
            }
        });
    });
});
</script>
<script>
    $(document).ready(function () {
        const switches = $('.toggle-switch-jc');

        switches.on('change', function () {
            if ($(this).is(':checked')) {
                // Uncheck all other switches
                switches.not(this).prop('checked', false);

                // Optional: Make an AJAX request to update the backend
                const id = $(this).data('id');
                updateCheckedToggleJc(id);
            }
        });

        function updateCheckedToggleJc(id) {
            var URL = '{{ route("admin.setting.schedule.save") }}';
            $.ajax({
                url: URL,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                contentType: 'application/json',
                data: JSON.stringify({ id: id }),
                success: function (response) {
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                },
            });
        }
    });
</script>
<script>
    $(document).ready(function () {
        const switches = $('.toggle-switch-ep');

        switches.on('change', function () {
            if ($(this).is(':checked')) {
                // Uncheck all other switches
                switches.not(this).prop('checked', false);

                // Optional: Make an AJAX request to update the backend
                const id = $(this).data('id');
                updateCheckedToggleEp(id);
            }
        });

        function updateCheckedToggleEp(id) {
            var URL = '{{ route("admin.setting.schedule.save") }}';
            $.ajax({
                url: URL,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                contentType: 'application/json',
                data: JSON.stringify({ id: id }),
                success: function (response) {
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                },
            });
        }
    });
</script>
@endpush
