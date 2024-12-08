@extends("messenger.layouts.app")
@section("contents")

<section class="wsus__chat_app show_info">

    @include("messenger.layouts.user-list-sidebar")

    <div class="wsus__chat_area">

        <!-- <div class="wsus__message_paceholder d-none"></div> -->
        <div class="wsus__message_paceholder black">
            <p class="p-2 bg-light text-center"> Select user to start conversation</p>
        </div>

        <div class="wsus__chat_area_header">
            <div class="header_left messenger-header">
                <span class="back_to_list">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <img src="assets/images/author_img_2.jpg" alt="User" class="img-fluid">
                <h4>Jubaydul islam</h4>
            </div>
            <div class="header_right">
                <a href="#" class="favourite"><i class="fas fa-star"></i></a>
                <a href="#" class="go_home"><i class="fas fa-home"></i></a>
                <a href="#" class="info"><i class="fas fa-info-circle"></i></a>
            </div>
        </div>

        <div class="wsus__chat_area_body">


        </div>

        <div class="wsus__chat_area_footer">
            <div class="footer_message">
                <div class="img d-none attachment-block">
                    <img src="{{ asset('assets/images/chat_img.png') }}" alt="User" class="img-fluid attachment-preview">
                    <span class="cancel-attachment"><i class="far fa-times"></i></span>
                </div>
                <form action="#" class="message-form" enctype="multipart/form-data">
                    <div class="file">
                        <label for="file"><i class="far fa-plus"></i></label>
                        <input id="file" type="file" hidden class="attachment-input" name="attachment" />
                    </div>
                    <textarea id="example1" rows="1" placeholder="Type a message.." class="message-input" name="message"></textarea>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>

    @include("messenger.layouts.user-info-sidebar")

</section>
@endsection