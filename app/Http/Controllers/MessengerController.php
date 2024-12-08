<?php

namespace App\Http\Controllers;

use App\Events\Message as EventsMessage;
use App\Models\Favorite;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use phpDocumentor\Reflection\Types\Null_;

use function Pest\Laravel\get;
use function Pest\Laravel\json;

class MessengerController extends Controller
{
    use FileUploadTrait;
    function index(): View
    {
        $favoritesList = Favorite::join("users", function ($join) {
            $join->on("favorites.favorite_id", "=", "users.id")
                ->where("favorites.user_id", "=", Auth::user()->id);
        })
            ->where("users.id", "!=", Auth::user()->id)
            ->select("users.*", DB::raw("MAX(favorites.created_at) as max_favorite_date"))
            ->groupBy("users.id", "users.name")
            ->orderBy("max_favorite_date", "desc")
            ->get();
        return view("messenger.index", compact("favoritesList"));
    }
    function search(Request $request)
    {

        $getRecords = null;
        $input = $request['query'];
        $records = User::where("id", "!=", Auth::user()->id)
            ->where("name", "LIKE", "%{$input}%")
            ->paginate(10);

        if ($records->total() < 1) {
            $getRecords .= "<p class='text-center'>Nothing to show</p>";
        }
        foreach ($records as $record) {
            $getRecords .= view("messenger.components.search-item", compact("record"))->render();
        };

        return  response()->json([
            'records' => $getRecords,
            "last_page" => $records->lastPage()
        ]);
    }
    function fetchIdInfo(Request $request)
    {


        $id = $request["id"];
        $record = User::where("id", "=", $id)->first();
        $favorite = Favorite::where(["user_id" => Auth::user()->id, "favorite_id" => $record->id])->exists();
        $sharedPhotos = Message::where(function ($query) use ($id) {
            $query->where("from_id", Auth::user()->id)
                ->where("to_id", $id)
                ->whereNotNull("attachment");
        })
            ->orWhere(function ($query) use ($id) {
                $query->where("from_id", $id)
                    ->where("to_id", Auth::user()->id)
                    ->whereNotNull("attachment");
            })

            ->get();

        $contents = "";
        foreach ($sharedPhotos as $sharedPhoto) {
            $contents .= view("messenger.components.gallery-item", compact("sharedPhoto"))->render();
        }
        return response()->json([
            'record' => $record,
            "favorite" => $favorite,
            "contents" => $contents
        ]);
    }


    function sendMessage(Request $request)
    {
        $request->validate([
            "message" => ["required"],
            "id" => ["required"],
            "temporaryMsgId" => ["required"],
            "attachment" => ["nullable", 'max:1024', "image"]
        ]);
        // store in database
        $attachmentPath = $this->uploadFile($request, "attachment");
        $message = new Message();
        $message->from_id = Auth::user()->id;
        $message->to_id = $request->id;
        $message->body = $request->message;
        if ($attachmentPath) {
            $message->attachment = json_encode($attachmentPath);
        };
        $message->save();

        // broadcast event
        EventsMessage::dispatch($message);



        // response
        return response()->json([
            'message' => $message->attachment ? $this->messageCard($message, true) : $this->messageCard($message),
            'tempId' => $request->temporaryMsgId
        ]);
    }
    function messageCard($message, $attachment = false)
    {

        return view("messenger.components.message-card", compact("message", "attachment"))->render();
    }
    function fetchMessages(Request $request)
    {
        $messages =  Message::where("from_id", Auth::user()->id)->where("to_id", $request->id)
            ->orWhere("from_id", $request->id)->where("to_id",  Auth::user()->id)
            ->latest()->paginate(20);
        $response = [
            "last_page" => $messages->lastPage(),
            "messages" => ""
        ];
        $allMessages = '';
        foreach ($messages->reverse() as $message) {
            $allMessages .= $this->messageCard($message, $message->attachment ? true : false);
        }
        $response["messages"] =  $allMessages;
        return  response()->json($response);
    }
    function getContacts(Request $request)
    {

        $users = Message::join("users", function ($join) {
            $join->on("messages.from_id", "=", "users.id")->orOn("messages.to_id", "=", "users.id");
        })->where(function ($q) {
            $q->where("messages.from_id", Auth::user()->id)
                ->orWhere("messages.to_id", Auth::user()->id);
        })->where("users.id", "!=", Auth::user()->id)
            ->select("users.*", DB::raw("MAX(messages.created_at) max_created_at"))
            ->orderBy("max_created_at", "desc")
            ->groupBy("users.id")
            ->get();

        if ($users->count() > 0) {
            $contacts = "";
            foreach ($users as $user) {
                $contacts .=  $this->getContactItem($user);
            }
        }
        return response()->json([
            "contacts" => $contacts,
            "users" => $users
        ]);
    }
    function getContactItem($user)
    {
        $lastMessage = Message::where("from_id", Auth::user()->id)->where("to_id", $user->id)
            ->orWhere("from_id", $user->id)->where("to_id",  Auth::user()->id)
            ->latest()->first();
        $unSeenCounter = Message::where("from_id", $user->id)->where('to_id', Auth::user()->id)->where("seen", 0)
            ->count();
        return view("messenger.components.contact-list-item", compact("lastMessage", "unSeenCounter", "user"))->render();
    }
    function makeSeen(Request $request)
    {
        $id = $request->id;
        Message::where("from_id", $id)->where('to_id', Auth::user()->id)->where("seen", 0)
            ->update([
                "seen" => 1
            ]);
        return true;
    }
    // add and remove favorite
    function favorite(Request $request)
    {
        $query = Favorite::where([
            "user_id" => Auth::user()->id,
            "favorite_id" => $request->id
        ]);
        $favoriteStatus = $query->exists();

        if (!$favoriteStatus) {
            $star = new Favorite();
            $star->user_id = Auth::user()->id;
            $star->favorite_id = $request->id;
            $star->save();
            return response([
                "status" => "added"
            ]);
        } else {
            $query->delete();
            return response()->json([
                "status" => "removed"
            ]);
        }
    }
    function fetchFavoritesList(Request $request)
    {

        $favorites = Favorite::join("users", function ($join) {
            $join->on("favorites.favorite_id", "=", "users.id")
                ->where("favorites.user_id", "=", Auth::user()->id);
        })
            ->where("users.id", "!=", Auth::user()->id)
            ->select("users.*", DB::raw("MAX(favorites.created_at) as max_favorite_date"))
            ->groupBy("users.id", "users.name")
            ->orderBy("max_favorite_date", "desc")
            ->get();
        $favoriteCard = "";
        foreach ($favorites as $user) {
            $favoriteCard .= $this->getFavouriteItem($user);
        }
        return response()->json([
            "favorites" => $favoriteCard,
            "records" => $favorites
        ]);
    }
    function deleteMessage(Request $request)
    {
        $message = Message::findOrFail($request->message_id);
        if ($message->from_id == Auth::user()->id) {
            $message->delete();
            return response()->json([
                "id" => $request->message_id,
            ], 200);
        }
        return;
    }
}
