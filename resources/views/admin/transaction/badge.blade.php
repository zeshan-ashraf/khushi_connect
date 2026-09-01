@if($type == 'success')
    <span class="badge bg-success text-capitalize">{{$type}}</span>
@elseif($type == 'pending')
    <span class="badge bg-primary text-capitalize">{{$type}}</span>
@elseif($type == 'failed')
    <span class="status-badge-wrap d-inline-flex align-items-center">
        <span class="badge bg-danger text-capitalize"
              data-bs-toggle="tooltip"
              title="{{ $reason }}">
            {{ $type }}
        </span>
        <button type="button"
                class="copy-btn ms-25"
                data-text="{{ e($reason) }}"
                title="Copy reason"
                aria-label="Copy reason">
            <i class="fas fa-copy"></i>
        </button>
    </span>
@elseif($type == 'reverse')
    <span class="badge bg-secondary text-capitalize text-status">{{$type}}</span>
@elseif($type == 'blocked')
    <span class="badge bg-info text-capitalize text-status" data-bs-toggle="tooltip" data-bs-placement="top" title="{{$reason}}">{{$type}}</span>
@else
    <span class="badge bg-warning text-capitalize text-status">{{$type}}</span>
@endif
