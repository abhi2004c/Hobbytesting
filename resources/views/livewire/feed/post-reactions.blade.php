<div class="flex items-center gap-2">
    @foreach(['like' => '👍', 'love' => '❤️', 'wow' => '😮', 'haha' => '😂'] as $type => $emoji)
        <button wire:click="react('{{ $type }}')"
                class="flex items-center gap-1 px-2.5 py-1.5 rounded-full text-sm transition-all hover:scale-105 {{ ($summary[$type] ?? 0) > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
            <span>{{ $emoji }}</span>
            @if(($summary[$type] ?? 0) > 0)
                <span class="text-xs font-semibold">{{ $summary[$type] }}</span>
            @endif
        </button>
    @endforeach
</div>
