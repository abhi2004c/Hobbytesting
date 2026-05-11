<x-layouts.app :title="'Messages — HobbyHub'">
    <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden" style="height: calc(100vh - 8rem);">
        <div class="flex h-full" x-data="{ showChat: false }" @open-conversation.window="showChat = true">
            {{-- Conversation List --}}
            <div class="w-full md:w-80 border-r border-gray-200 flex flex-col h-full" :class="{ 'hidden md:flex': showChat }">
                @livewire('messaging.conversation-list')
            </div>

            {{-- Chat Window --}}
            <div class="flex-1 flex flex-col h-full w-full absolute md:relative bg-white" :class="{ 'hidden md:flex': !showChat, 'flex z-50': showChat }">
                @livewire('messaging.chat-window')
            </div>
        </div>
    </div>
</x-layouts.app>
