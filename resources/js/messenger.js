/***
 * -------------------
 *Global Variables
 * -----------------
 */
var temporaryMsgId = 0;
var activeUserIds = [];
const messageForm = $(".message-form"),
    messageContactBox = $(".wsus__user_list_area_height"),
    messageInput = $(".message-input"),
    messageBoxContainer = $(".wsus__chat_area_body"),
    url = $("meta[name=url]").attr("content"),
    csrf_token = $("meta[name=csrf_token]").attr("content");

const getMessengerId = () => $("meta[name=id]").attr("content");
const setMessengerId = (id) => $("meta[name=id]").attr("content", id);

/***
 * -------------------
 * Resuable Functions
 * -----------------
 */

function imagePreview(input, selector) {
    if (input.files && input.files[0]) {
        var render = new FileReader();

        render.onload = function (e) {
            $(selector).attr("src", e.target.result);
        };

        render.readAsDataURL(input.files[0]);
    }
}
function enableCHatBoxLoader() {
    $(".wsus__message_paceholder").addClass("d-none");
}
function disableCHatBoxLoader() {
    $(".wsus__chat_app").removeClass("show_info");
    $(".wsus__message_paceholder").removeClass("d-none");
}
function showChatBox() {
    $(".wsus__message_paceholder.black").addClass("d-none");
}
function hideChatBox() {
    $(".wsus__message_paceholder.black").removeClass("d-none");
}
/**
 * --------
 * Search list Function
 * -------
 */
let searchPage = 1;
let noMoreDataSearch = false;
let searchTempVal = "";
function searchUser(query) {
    if (query != searchTempVal) {
        searchPage = 1;
        noMoreDataSearch = false;
    }
    searchTempVal = query;
    if (!noMoreDataSearch) {
        $.ajax({
            method: "GET",
            url: "messenger/search",
            data: { query: query, page: searchPage },
            beforeSend: function () {
                let loader = `<div class="spinner-border text-light" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>`;
                $(".user_search_list_result").append(loader);
            },
            success: function (data) {
                console.log(data.records);
                if (searchPage < 2) {
                    $(".user_search_list_result").html(data.records);
                } else {
                    $(".user_search_list_result").append(data.records);
                }
                noMoreDataSearch = searchPage >= data?.last_page;

                if (!noMoreDataSearch) {
                    searchPage++;
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr);
                console.log(error);
                console.log(status);
            },
        });
    }
}
function actionOnScroll(selector, callback, topScroll = false) {
    $(selector).on("scroll", function () {
        let element = $(this).get(0);
        element.style.maxHeight = "750px";
        const condition = topScroll
            ? element.scrollTop == 0
            : element.scrollTop + element.clientHeight >= element.scrollHeight;
        if (condition) {
            callback();
        }
        // console.log(element.scrollTop);
        // console.log(
        //     element.scrollTop + element.clientHeight >= element.scrollHeight
        // );
    });
}
/**
 * -----------
 * Debounce function
 * --------
 */
const debounce = (callback, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            callback.apply(null, args);
        }, delay);
    };
};
/**
 * -----------
 * Fetch Id data of user and update the view
 * ----------------------------------------------------------------
 */
function IDinfo(id) {
    $.ajax({
        method: "GET",
        url: "messenger/id-info",
        data: { id: id },
        beforeSend: function () {
            NProgress.start();
            disableCHatBoxLoader();
        },
        success: function (data) {
            console.log(data.favorite);
            // console.log(data.record[0].id);
            fetchMessage(data.record.id, true);
            // console.log(data.record[0]);
            if (data.contents) {
                $(".nothing_share").addClass("d-none");
                $(".shared-photo-gallery").html(data.contents);
            } else {
                $(".nothing_share").removeClass("d-none");
                $(".shared-photo-gallery").html("");
            }
            data.favorite
                ? $(".favourite").addClass("inactive")
                : $(".favourite").removeClass("active");
            $(".messenger-header").find("img").attr("src", data.record.avatar);
            $(".messenger-header").find("h4").text(data.record.name);
            $(".messenger-info-view .user_photo")
                .find("img")
                .attr("src", data.record.avatar);
            $(".messenger-info-view").find(".user_name").text(data.record.name);

            NProgress.done();
            enableCHatBoxLoader();
            showChatBox();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        },
    });
}
/**
 * -----------
 * Send Message
 * ----------------------------------------------------------------
 */

function sendMessageCard(message, tempId, attachment = false) {
    if (attachment) {
        return `<div class="wsus__single_chat_area message-card" data-id="${tempId}">
        <div class="wsus__single_chat chat_right">
                   <a class="venobox" data-gall="gallery${
                       e.id
                   }" href="{{ $imagePath }}">
            <img src="{{ $imagePath }}" alt="gallery1" class="img-fluid w-100">
        </a>
                    
                          ${
                              message.length > 0
                                  ? `<p class="messages">${message}}</p>`
                                  : ``
                          } 
                     
                     <span class="clock"><i class="fas fa-clock"></i> Sending...</span>
                    <a class="action" href="#"><i class="fas fa-trash"></i></a>
                </div>
                </div>`;
    } else {
        return ` <div class="wsus__single_chat_area message-card">
        <div class="wsus__single_chat chat_right " data-id=${tempId}>
            <p class="messages">${message}</p>
            <span class="clock"><i class="fas fa-clock"></i> now</span>
            <a class="action" href="#"><i class="fas fa-trash"></i></a>
        </div>
    </div>`;
    }
}

function sendMessage(id) {
    temporaryMsgId++;
    let hasAttachment = !!$(".attachment-input").val();
    let tempID = `temp_${temporaryMsgId}`;
    const inputValue = messageInput.val();

    if (inputValue.length > 0 || hasAttachment) {
        const formData = new FormData($(".message-form")[0]);

        formData.append("id", getMessengerId());
        formData.append("temporaryMsgId", tempID);
        formData.append("_token", csrf_token);
        // console.log(sendMessageCard(inputValue, tempID));

        $.ajax({
            method: "POST",
            url: "/messenger/send-message",
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $(".emojionearea-editor").text("");
            },
            success: function (data) {
                const tempMsgCardElement = $(
                    `.wsus__single_chat[data-id="${tempID}"]`
                );
                console.log(tempMsgCardElement);

                // Remove the entire message card (its parent element)
                tempMsgCardElement.closest(".wsus__single_chat_area").remove();

                // Append the new message to the container

                messageBoxContainer.append(data.message);

                messageFormReset();
                scrollToBottom(messageBoxContainer);
                getContacts();
            },
            error: function (xhr, status, error) {
                console.log(xhr);
                console.log(error);
            },
        });
    }
}

function receivedMessageCard(e) {
    if (e.attachment) {
        return `<div class="wsus__single_chat_area message-card" data-id="${
            e.id
        }">
        <div class="wsus__single_chat ">
                     <a class="venobox" data-gall="gallery${e.id}}" href="${
            url + e.attachment
        }">
            <img src="${
                url + e.attachment
            }" alt="gallery1" class="img-fluid w-100">
        </a>
                    
                          ${
                              e.message > 0
                                  ? `<p class="messages 'bg-secondary text-dark'">${e.message}}</p>`
                                  : ``
                          } 
                     
                     <span class="clock"><i class="fas fa-clock"></i> now</span>
                    <a class="action" href="#"><i class="fas fa-trash"></i></a>
                </div>
                </div>`;
    } else {
        return ` <div class="wsus__single_chat_area message-card" data-id=${e.id}>
        <div class="wsus__single_chat" data-id=${e.id}>
            <p class="messages bg-secondary text-dark">${e.message}</p>
            <span class="clock"><i class="fas fa-clock"></i> now</span>
            <a class="action" href="#"><i class="fas fa-trash"></i></a>
        </div>
    </div>`;
    }
}

function messageFormReset() {
    const previewImage = $(".attachment-block");
    previewImage.addClass("d-none");
    messageForm.trigger("reset");
    console.log("working");
}
function cancelAttachments() {}

/**
 * ---------------
 * Fetch Message from Database
 *----------------
 */
let messagesPage = 1;
let noMoreMessages = false;
let messagesLoading = false;
function fetchMessage(id, newFetch = false) {
    if (newFetch) {
        messagesPage = 1;
        noMoreMessages = false;
    }
    if (!noMoreMessages) {
        $.ajax({
            method: "GET",
            url: "messenger/fetch-message",
            data: { _token: csrf_token, id: id, page: messagesPage },
            beforeSend: function () {
                let loader = `
                
                <div class="spinner-border text-light" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                       `;
                messageBoxContainer.prepend(loader);
            },
            success: function (data) {
                makeSeen(true);
                if (messagesPage === 1) {
                    messageBoxContainer.html(data.messages);
                    scrollToBottom(messageBoxContainer);
                } else {
                    messageBoxContainer.prepend(data.messages);
                }

                //pagination lock and page increment

                noMoreMessages = messagesPage >= data?.last_page; // return true if data?.last_page is less than initial and true if ... is more than ...

                if (!noMoreMessages && messagesPage <= data?.last_page)
                    messagesPage++;
            },
            error: function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
        });
    }
}
/**
 * -----------
 * Make Seen Messages
 * ----------------------------------------------------------------
 */
function makeSeen(status) {
    const box = $(`.messenger-list_item[data-id="${getMessengerId()}]`);
    $(`.messenger-list-item[data-id="${getMessengerId()}"]`)
        .find(".unseen_count")
        .remove();
    console.log(box);
    $.ajax({
        method: "GET",
        url: "messenger/make-seen",
        data: { _token: csrf_token, id: getMessengerId() },
        success: function (data) {
            console.log(data);
        },
        error: function (xhr, status, error) {
            console.log(xhr);
        },
    });
}
/**
 * -----------
 * add or remove favorite users
 * ----------------------------------------------------------------
 */
function favorite(user_id) {
    $(`.favourite`).toggleClass("active");
    $.ajax({
        method: "POST",
        url: "messenger/favorite",
        data: { _token: csrf_token, id: user_id },
        success: function (data) {
            console.log(data.status);
            if (data.status == "added") {
                notyf.success("Added to favorites list.");
            } else {
                notyf.success("Removed favorites list.");
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr);
        },
    });
}
/**
 * -----------
 * fetch favorite
 * ----------------------------------------------------------------
 */
function getFavoriteList(user_id) {
    $.ajax({
        method: "GET",
        url: "messenger/fetch-favorite",
        data: { _token: csrf_token, id: user_id },
        success: function (data) {
            console.log(data.records);
            $(".favourite_user_slider").append(data.favorites);
        },
        error: function (xhr, status, error) {
            console.log(xhr);
        },
    });
}
/**
 * ---------------
 * Scroll to Bottom
 *----------------
 */
function scrollToBottom(container) {
    $(container)
        .stop()
        .animate({
            scrollTop: $(container)[0].scrollHeight,
        });
    console.log("scroll");
}
/**
 * ---------------
 * Fetch Contacts
 *----------------
 */
let contactPage = 1;
let noMoreContacts = false;
let contactLoading = false;
function getContacts() {
    $.ajax({
        method: "GET",
        url: "messenger/fetch-contacts",
        data: { page: contactPage },
        beforeSend: function () {
            contactLoading = true;
        },
        success: function (data) {
            messageContactBox.html(data.contacts);
            const users = data.users;
            users.forEach((user) => {
                if (activeUserIds.includes(user.id)) {
                    $(`.messenger-list-item[data-id=${user.id}]`)
                        .find("span")
                        .removeClass("inactive");
                    //
                    $(`.messenger-list-item[data-id=${user.id}]`)
                        .find("span")
                        .addClass("active");
                } else {
                    $(`.messenger-list-item[data-id=${user.id}]`)
                        .find("span")
                        .removeClass("active");
                    //
                    $(`.messenger-list-item[data-id=${user.id}]`)
                        .find("span")
                        .addClass("inactive");
                }
            });
        },
        error: function (xhr, status, error) {
            contactLoading = true;
            console.log(xhr, error);
        },
    });
}
function updatedSelectedContact(user_id) {
    $(".messenger-list-item").removeClass("active");
    $(`.messenger-list-item[data-id=${user_id}]`).addClass("active");
}
/**
 * ---------------
 * Delete Message
 *----------------
 */
function deleteMessage(message_id) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                method: "DELETE",
                url: "messenger/delete-message",
                data: { _token: csrf_token, message_id: message_id },
                success: function (data) {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your file has been deleted.",
                        icon: "success",
                    });
                    $(`.message-card[data-id="${message_id}"]`).remove();
                },
                error: function (xhr, status, error) {
                    console.log(xhr, status, error);
                },
            });
        }
    });
}

/**
 * ---------------
 * Play message sound
 *----------------
 */
function playNotificationSound() {
    const sound = new Audio(`default/message-sound.mp3`);
    sound.play().catch((error) => {
        console.error("Lỗi phát âm thanh:", error);
    });
}
/**
 *
 *
 *
 */

// Listen to message chanel
window.Echo.private("message." + auth_id)
    .subscribed(() => {
        console.log(`Đã đăng ký vào kênh ${auth_id} thành công!`);
    })
    .listen("Message", (e) => {
        let message = receivedMessageCard(e);
        console.log(e.message);
        if (getMessengerId() == e.from_id) messageBoxContainer.append(message);
        getContacts();
        playNotificationSound();
    })
    .error((error) => {
        console.error("Lỗi khi kết nối vào kênh:", error);
    });

// Listen to online chanel

window.Echo.join("online")
    .here((users) => {
        setActiveUserIds(users);
        console.log(activeUserIds);
        $.each(users, function (index, user) {
            console.log(user);
            $(`.messenger-list-item[data-id=${user.id}]`)
                .find("span")
                .removeClass("inactive");
            //
            $(`.messenger-list-item[data-id=${user.id}]`)
                .find("span")
                .addClass("active");
            $(`.favorite-item[data-id=${user.id}]`)
                .find("span")
                .removeClass("inactive");

            $(`.favorite-item[data-id=${user.id}]`)
                .find("span")
                .addClass("active");
        });
    })
    .joining((user) => {
        console.log("Joining");
        addNewUserId(user.id);
        console.log(user);
        $(`.messenger-list-item[data-id=${user.id}]`)
            .find("span")
            .removeClass("inactive");
        //
        $(`.messenger-list-item[data-id=${user.id}]`)
            .find("span")
            .addClass("active");
    })
    .leaving((user) => {
        console.log("Leaving");
        removeUserId(user.id);

        console.log(user);
        $(`.messenger-list-item[data-id=${user.id}]`)
            .find("span")
            .removeClass("active");
        //
        $(`.messenger-list-item[data-id=${user.id}]`)
            .find("span")
            .addClass("inactive");
    });

function setActiveUserIds(users) {
    $.each(users, function (index, user) {
        activeUserIds.push(user.id);
    });
}
function addNewUserId(id) {
    activeUserIds.push(id);
}
function removeUserId(id) {
    let index = activeUserIds.indexOf(id);
    if (index !== -1) {
        activeUserIds.splice(index, 1);
    }
}
/***
 * -------
 * on Dom Load
 * ------
 */
// getFavoriteList();

$(document).ready(function () {
    getContacts();
    $("#select_file").change(function () {
        imagePreview(this, ".profile-image-preview");
    });
    // search action on keydown
    const debouncedSearch = debounce(function () {
        const value = $(".user-search").val();

        searchUser(value);
    }, 500);
    $(".user-search").on("keyup", function (e) {
        e.preventDefault();
        let element = $(".user_search_list_result").get(0);
        element.style.maxHeight = "750px";
        let query = $(this).val();
        if (query.length > 0) {
            debouncedSearch();
        }
    });
    $(".user-search").get(0).style.maxHeight = "750px";
    actionOnScroll(".user_search_list_result", function () {
        let value = $(".user_search").val();
        searchUser(value);
    });
    // click action on search item
    $("body").on("click", ".messenger-list-item", function () {
        const dataId = $(this).attr("data-id");
        setMessengerId(dataId);
        IDinfo(dataId);
        updatedSelectedContact(dataId);
    });
    $(".message-form").on("submit", function (e) {
        e.preventDefault();
        sendMessage();
    });
    $(".attachment-input").change(function () {
        // console.log(hasAttachment);
        imagePreview(this, ".attachment-preview");
        const previewImage = $(".attachment-block");
        previewImage.removeClass("d-none");
    });
    $(".cancel-attachment").on("click", function () {
        messageFormReset();
    });
    // search pagination
    actionOnScroll(
        ".wsus__chat_area_body",
        function () {
            fetchMessage(getMessengerId());
        },
        true
    );
    $(".favourite").on("click", function (e) {
        e.preventDefault();
        favorite(getMessengerId());
    });
    $("body").on("click", ".dlt-message", function (e) {
        e.preventDefault();
        let id = $(this).data("id");
        deleteMessage(id);
        console.log(this);
    });
});
