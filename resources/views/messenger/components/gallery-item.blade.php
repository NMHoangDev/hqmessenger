@php
$image = json_decode($sharedPhoto->attachment);
@endphp
<li>
    <a class="venobox" data-gall="gallery01" href="{{ asset($image) }}">
        <img src="{{ asset($image) }}" alt="gallery1" class="img-fluid w-100">
    </a>
</li>