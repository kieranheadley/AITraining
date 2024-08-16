@extends('layouts.app')

@section('title', 'Website Keyword Assignment')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Website Keyword Assignment</h1>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <span class="inline-flex items-center">
                    @if($website->process_stage == 0)
                        <span class="inline-flex items-center ml-2 px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded-full text-sm font-semibold text-gray-600 text-xs mr-4">
                                Not Started
                            </span>
                    @elseif($website->process_stage == 7)
                        <span class="inline-flex items-center ml-2 px-2 py-1 bg-green-200 hover:bg-green-300 rounded-full text-sm font-semibold text-green-600 text-xs mr-4">
                                Completed
                            </span>
                    @else
                        <span class="inline-flex items-center ml-2 px-2 py-1 bg-indigo-200 hover:bg-indigo-300 rounded-full text-sm font-semibold text-indigo-600 text-xs">
                                <svg class="animate-spin mr-0 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                    <span class="ml-1">
                                    {{ $website->getCurrentStage() }}
                                </span>
                            </span>
                    @endif

                    @if($website->process_stage == 0 || $website->process_stage == 7)
                        <a href="/website/{{ $website->id }}/process" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Run Assignment</a>
                    @endif
                </span>
            </div>
        </div>
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 text-center">Keyword</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center">Assigned Page</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center">New Page</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center">Selected</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center">Embedding Pages</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center"></th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($website->keywords->sortBy('assigned_page') as $keyword)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            {{ $keyword->keyword }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $keyword->assigned_page }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                            @if($keyword->new_page)
                                                <span class="inline-flex items-center m-2 px-3 py-1 bg-green-200 hover:bg-green-300 rounded-full text-sm font-semibold text-green-600">
                                                    Yes
                                                </span>
                                            @else
                                                <span class="inline-flex items-center m-2 px-3 py-1 bg-red-200 hover:bg-red-300 rounded-full text-sm font-semibold text-red-600">
                                                    No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                            @if($keyword->selected)
                                                <span class="inline-flex items-center m-2 px-3 py-1 bg-green-200 hover:bg-green-300 rounded-full text-sm font-semibold text-green-600">
                                                    Yes
                                                </span>
                                            @else
                                                <span class="inline-flex items-center m-2 px-3 py-1 bg-red-200 hover:bg-red-300 rounded-full text-sm font-semibold text-red-600">
                                                    No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @foreach($keyword->embedding_results ?? [] as $embedding_result)
                                                {{ $embedding_result['url'] }}
                                                <span class="inline-flex items-center ml-2 px-1 py-1 bg-gray-200 hover:bg-gray-300 rounded-full text-sm font-semibold text-gray-600 text-xs" title="Embedding Score: {{ round($embedding_result['score'],2) }}">
                                                    {{ round($embedding_result['score'],2) }}
                                                </span>
                                                <br>
                                            @endforeach
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <a href="/keyword/flag/{{ $keyword->id }}" class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                                </svg>

                                                <span class="ml-1">Flag</span>
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
@endsection
