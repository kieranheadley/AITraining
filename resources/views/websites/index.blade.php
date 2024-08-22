@extends('layouts.app')

@section('title', 'Websites')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Websites</h1>
            </div>
        </div>
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Website URL</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Crawl Log</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">SERP ID</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Process Stage</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($websites as $website)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            {{ $website->website_url }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $website->crawl_location }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $website->serp_id }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <span class="inline-flex items-center">
                                                @if($website->process_stage == 0)
                                                    <span class="inline-flex items-center ml-2 px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded-full text-sm font-semibold text-gray-600 text-xs">
                                                        Not Started
                                                    </span>
                                                @elseif($website->process_stage == 8)
                                                    <span class="inline-flex items-center ml-2 px-2 py-1 bg-green-200 hover:bg-green-300 rounded-full text-sm font-semibold text-green-600 text-xs">
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
                                            </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <a href="/website/{{ $website->id }}" class="text-indigo-600 hover:text-indigo-900">View</a>
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
