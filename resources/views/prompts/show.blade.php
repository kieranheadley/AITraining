@extends('layouts.app')

@section('title', 'Flagged Keywords')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="mt-4 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <h2 class="text-base font-semibold leading-6 text-gray-900">System Prompt</h2>
                    <textarea rows="10" class=" w-full h-2/6 border-2 border-blue-400 border-dashed rounded-md p-4 mt-2">{{ str_replace('\n', PHP_EOL, $systemPrompt) }}</textarea>

                    <h2 class="text-base font-semibold leading-6 text-gray-900 mt-8">User Prompt</h2>
                    <textarea rows="10" class="w-full h-2/6 border-2 border-blue-400 border-dashed rounded-md p-4 mt-2">{{ str_replace('\n', PHP_EOL, $userPrompt) }}</textarea>
                </div>
            </div>
        </div>
    </div>
@endsection
