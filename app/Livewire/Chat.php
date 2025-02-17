<?php

namespace App\Livewire;

use App\Events\MessageSentEvent;
use App\Events\UnreadMessage;
use App\Events\UserTyping;
use App\Models\Message;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;

    public $user;
    public $messages;
    public $message,$sender_id,$receiver_id;
    public $file;

    public function render()
    {
        $this->readAllMessages();
        return view('livewire.chat');
    }

    public function mount($userId){
        #dispatching event to scroll to the bottom<
        $this->dispatch('messages-updated');

        $this->user = $this->getUser($userId);

        $this->sender_id = auth()->id();
        $this->receiver_id = $userId;

        $this->messages = $this->getMessages();

        $this->readAllMessages();
    }

    public function getUser($userId)
    {
        return User::find($userId);
    }

    public function userTyping()
    {
        broadcast(new UserTyping($this->sender_id,$this->receiver_id))->toOthers();
    }

    public function readAllMessages(){
        Message::where('sender_id', $this->receiver_id)
        ->where('receiver_id', $this->sender_id)
        ->where('is_read',false)->update(['is_read' => true]);

    }


    public function sendMessage(){
        $sendMessage = $this->saveMessage();

        # Assigning latest message
        $this->messages[] = $sendMessage;

        # Broadcast message
        broadcast(new MessageSentEvent($sendMessage));

        $unreadMessageCount = $this->getUnreadMessagesCount();
        broadcast(new UnreadMessage($this->sender_id,$this->receiver_id,$unreadMessageCount))->toOthers();

        $this->message = null;
        $this->file = null;

        #dispatching event to scroll to the bottom
        $this->dispatch('messages-updated');
    }

    public function getUnreadMessagesCount(){
        return Message::where('receiver_id', $this->receiver_id)->where('is_read',false)->count();
    }

    // #[On('echo-private:chat-channel.{senderId},MessageSentEvent')]
    public function listenMessage($event)
    {
        $newMessage = Message::find($event['message']['id']);
        $this->messages[] = $newMessage;
    }

    public function getListeners()
    {
        return [
            "echo-private:chat-channel.{$this->sender_id},MessageSentEvent" => 'listenMessage',
        ];
    }

    public function saveMessage()
    {
        $fileName = $this->file?->hashName() ?? null;
        $fileOriginalName = $this->file?->getClientOriginalName() ?? null;
        $folderPath = $this->file ? $this->file->store('chat_files','public') : null;
        $fileType = $this->file?->getMimeType() ?? null;


        return Message::create([
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'message' => $this->message,
            'file_name' => $fileName,
            'file_original_name'=> $fileOriginalName,
            'folder_path' => $folderPath,
            'file_type' => $fileType,
            'is_read' => false
        ]) ;
    }

    public function getMessages(){
        return Message::with('sender:id,name,','receiver:id,name')
            ->where(function($q){
                $q->where('sender_id',$this->sender_id)->where('receiver_id',$this->receiver_id);
            })
            ->orWhere(function($q){
                $q->where('sender_id',$this->receiver_id)->where('receiver_id',$this->sender_id);
            })
        ->get();
    }

}
