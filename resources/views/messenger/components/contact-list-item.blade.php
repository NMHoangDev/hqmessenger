<div class="wsus__user_list_item messenger-list-item" data-id="{{ $user->id }}">
    <div class=" img">
        <img src="{{ $user->avatar }}" alt="User" class="img-fluid">
        <span class="inactive"></span>
    </div>
    <div class="text">
        <h5>{{ $user->name }}</h5>
        @if($lastMessage->from_id == auth()->user()->id)
        <p><span>You</span> {{$lastMessage->body}}</p>
        @else
        <p><span>{{ $user->name }}</span> {{$lastMessage->body}}</p>

        @endif
    </div>
    @if($unSeenCounter !== 0)
    <span class="badge text-light bg-danger unseen_count">{{ $unSeenCounter }}</span>
    @endif
</div>