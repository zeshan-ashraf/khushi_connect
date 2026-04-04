@if($type == 'success')
    <span class="badge bg-success text-capitalize">{{$type}}</span>
@elseif($type == 'pending')
    <span class="badge bg-primary text-capitalize">{{$type}}</span>
@elseif($type == 'failed')
    <span class="badge bg-danger text-capitalize"
          data-bs-toggle="tooltip"
          data-bs-placement="top"
          title="{{ $reason }}">
        {{ $type }}
    </span>

    <i class="bi bi-clipboard copy-btn"
    style="cursor:pointer;"
    data-text="{{ $reason }}"
    title="Copy reason"></i>
@else
    <span class="badge bg-info text-capitalize">{{$type}}</span>
@endif

