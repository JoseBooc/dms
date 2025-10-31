@php
    $record = $getRecord();
    $isCompleted = $record->status === 'completed';
    $hasProof = false;
    $validProofs = [];
    
    // Safely handle completion_proof data
    if ($isCompleted && $record->completion_proof) {
        $completionProof = $record->completion_proof;
        
        // Handle different data types
        if (is_string($completionProof)) {
            // If it's a JSON string, decode it
            $decoded = json_decode($completionProof, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $completionProof = $decoded;
            } else {
                // If it's a single file path string
                $completionProof = [$completionProof];
            }
        }
        
        // Ensure it's an array and filter valid string elements
        if (is_array($completionProof)) {
            $validProofs = array_filter($completionProof, function($item) {
                return is_string($item) && !empty(trim($item));
            });
            $hasProof = count($validProofs) > 0;
        }
    }
@endphp

@if($isCompleted && $hasProof)
    <div class="flex items-center space-x-2">
        <div class="flex -space-x-1">
            @foreach(array_slice($validProofs, 0, 3) as $index => $proof)
                @if(is_string($proof) && !empty($proof))
                    <img 
                        src="{{ \Illuminate\Support\Facades\Storage::url($proof) }}" 
                        alt="Completion Proof {{ $index + 1 }}"
                        class="w-8 h-8 rounded-full border-2 border-white object-cover cursor-pointer hover:scale-110 transition-transform"
                        onclick="openProofModal('{{ $record->id }}')"
                    >
                @endif
            @endforeach
            @if(count($validProofs) > 3)
                <div class="w-8 h-8 rounded-full border-2 border-white bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-600">
                    +{{ count($validProofs) - 3 }}
                </div>
            @endif
        </div>
        <button 
            onclick="openProofModal('{{ $record->id }}')"
            class="text-sm text-blue-600 hover:text-blue-800 underline"
        >
            View ({{ count($validProofs) }})
        </button>
    </div>

    <!-- Modal for viewing completion proof -->
    <div id="proof-modal-{{ $record->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeProofModal('{{ $record->id }}')"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Completion Proof - Request #{{ $record->id }}
                        </h3>
                        <button onclick="closeProofModal('{{ $record->id }}')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    @if($record->completion_notes)
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Completion Notes:</h4>
                            <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded">{{ $record->completion_notes }}</p>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($validProofs as $index => $proof)
                            @if(is_string($proof) && !empty($proof))
                                <div class="relative">
                                    <img 
                                        src="{{ \Illuminate\Support\Facades\Storage::url($proof) }}" 
                                        alt="Completion Proof {{ $index + 1 }}"
                                        class="w-full h-48 object-cover rounded-lg border border-gray-200"
                                    >
                                    <div class="absolute top-2 left-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">
                                        {{ $index + 1 }} / {{ count($validProofs) }}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button 
                        onclick="closeProofModal('{{ $record->id }}')"
                        class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openProofModal(recordId) {
            document.getElementById('proof-modal-' + recordId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeProofModal(recordId) {
            document.getElementById('proof-modal-' + recordId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
@else
    <span class="text-gray-400 text-sm">No proof uploaded</span>
@endif