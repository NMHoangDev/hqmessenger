@if ($attachment)
@php
$imagePath= json_decode($message->attachment);
@endphp
<div class="wsus__single_chat_area message-card" data-id="{{ $message->id }}">
    <div class="wsus__single_chat {{ $message->from_id === auth()->user()->id ? 'chat_right' : '' }}">

        <a class="venobox" data-gall="gallery01" href="{{ $imagePath }}">
            <img src="{{ $imagePath }}" alt="gallery1" class="img-fluid w-100">
        </a>
        <p class="messages {{ $message->from_id === auth()->user()->id ? '' : 'bg-secondary text-dark' }}">{{ $message->body }}</p>
        <span class="time">{{ \Carbon\Carbon::parse($message->created_at)->diffForHumans()  }}</span>
        <a class="action dlt-message" href="" data-id="{{ $message->id }}"><i class="fas fa-trash"></i></a>
    </div>
</div>
@else
<div class="wsus__single_chat_area  message-card" data-id="{{ $message->id }}">
    <div class="wsus__single_chat {{ $message->from_id === auth()->user()->id ? 'chat_right' : '' }} ">
        <p class="messages {{ $message->from_id === auth()->user()->id ? '' : 'bg-secondary text-dark' }}">{{ $message->body }}</p>
        <span class="time"> {{ \Carbon\Carbon::parse($message->created_at)->diffForHumans()  }}</span>
        <a class="action dlt-message" href="" data-id="{{ $message->id }}"><i class="fas fa-trash"></i></a>
    </div>
</div>

@endif