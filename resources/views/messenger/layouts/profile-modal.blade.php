<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <form action="#" class="profile-form" enctype="multipart/form-data">
                    @csrf
                    <div class="file file-profile">
                        <img src="{{ asset(auth()->user()->avatar) }}" alt="Upload" class="img-fluid profile-image-preview">
                        <label for="select_file"><i class="fal fa-camera-alt"></i></label>
                        <input id="select_file" type="file" hidden name="avatar">
                    </div>
                    <p>Edit information</p>
                    <input type="text" placeholder="Name" value="{{ auth()->user()->name }}" name="name">
                    <input type="email" placeholder="Email" value="{{ auth()->user()->email }}" name="email">
                    <input type="text" placeholder="User ID" value="{{ auth()->user()->id }}" name="id">
                    <p>Change password</p>
                    <div class="row">
                        <div class="col-xl-6">
                            <input type="password" placeholder="Current Password" name="current_password">
                        </div>
                        <div class="col-xl-6">
                            <input type="password" placeholder="New Password" name="password">
                        </div>
                        <div class="col-xl-12">
                            <input type="password" placeholder="Confirm Password" name="password_confirmation">
                        </div>
                    </div>
                    <div class="modal-footer p-0 mt-4">
                        <button type="button" class="btn btn-secondary cancel"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary save">Save changes</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@push("scripts")
<script>
    $(document).ready(function() {
        $(".profile-form").on("submit", function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                method: "POST",
                url: '{{route("profile.update")}}',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    const errors = jqXHR.responseJSON.errors;
                    $.each(errors, function(index, value) {
                        notyf.error(value[0]);
                    })
                }
            })
        })
    })
</script>
@endpush