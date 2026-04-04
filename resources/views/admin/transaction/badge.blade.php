@if($type == 'success')
    <span class="badge bg-success text-capitalize">{{$type}}</span>
@elseif($type == 'pending')
    <span class="badge bg-primary text-capitalize">{{$type}}</span>
@elseif($type == 'failed')
    <span class="badge bg-danger text-capitalize d-inline-block"
      data-bs-toggle="tooltip"
      title="{{ $reason }}">
    {{ $type }}
</span>

<button type="button"
        class="btn btn-sm btn-light border ms-1 copy-btn text-nowrap d-inline-block"
        data-text="{{ e($reason) }}">
    Copy
</button>
@else
    <span class="badge bg-info text-capitalize">{{$type}}</span>
@endif

