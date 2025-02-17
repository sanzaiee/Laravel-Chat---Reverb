<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container max-auto p-8">
                    <h1 class="text-2xl font-bold mb-4">
                        List of users
                    </h1>

                    <div class="overflow-x">
                        <table class="table-auto w-full border-collapse border border-gray-300 ">
                            <thead>
                                <tr class="bg-gray-0">
                                    <th class="border border-gray-300 x-4py-2 text-left w-12">#</th>
                                    <th class="border border-gray-300 x-4py-2 text-left">Name</th>
                                    <th class="border border-gray-300 x-4py-2 text-left">Email</th>
                                    <th class="border border-gray-300 x-4py-2 text-left">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2">{{ $loop->index +1 }}</td>
                                        <td class="border border-gray-300 px-4 py-2"> {{ $user->name }}</td>
                                        <td class="border border-gray-300 px-4 py-2"> {{ $user->email }}</td>
                                        <td class="border border-gray-300 px-4 py-2 relative">
                                            <a navigate href="{{ route('chat',$user->id) }}">Chat

                                                <span id="unread-count-{{ $user->id }}" class="{{ $user->unread_message_count > 0 ? 'absolute right-11 bg-red-600 text-white px-2 py-1 rounded-full text-xs font-bold' : ''}}">
                                                    {{ $user->unread_message_count > 0 ? $user->unread_message_count : null }}
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script type="module">
    window.Echo.private('unread-channel.{{ auth()->id() }}')
    .listen('UnreadMessage',(event)=>{
        const unreadElementCount = document.getElementById(`unread-count-${event.senderId}`);
        if(unreadElementCount){
            unreadElementCount.classList = event.unreadMessageCount > 0 ? 'absolute right-11 bg-red-600 text-white px-2 py-1 rounded-full text-xs font-bold' : '';
            unreadElementCount.textContent = event.unreadMessageCount > 0 ? event.unreadMessageCount : 0;
        }

        //play notification audio
        if(event.unreadMessageCount > 0){
            const audio = new Audio('{{ asset('sound/notification.wav') }}');
            audio.play();
        }
    });
</script>
