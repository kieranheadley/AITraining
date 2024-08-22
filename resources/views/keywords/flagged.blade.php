@extends('layouts.app')

@section('title', 'Flagged Keywords')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Flagged Keywords ({{ $keywords->count() }})</h1>
            </div>
        </div>
        <div class="mt-4 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 text-center">Keyword</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center">Assigned Page</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center">Embedding Pages</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center">Flagged Reason</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($keywords as $keyword)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            <p>{{ $keyword->keyword }}</p>
                                            <p class="inline-flex text-blue-500 mt-2">
                                                <a href="/website/{{ $keyword->website_id }}">{{ parse_url($keyword->website->website_url)['host'] }}</a>
                                                <a href="{{ $keyword->website->website_url }}" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                    </svg>
                                                </a>
                                            </p>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <strong>AI:</strong> {{ $keyword->assigned_page }} <br>
                                            <small>
                                                Hike:  {{ str_replace(rtrim($keyword->website->website_url, '/'), '', $keyword->hike_assigned_page) }}
                                            </small>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if(stristr($keyword->selection_reason, 'Ranking Position'))
                                                {{ $keyword->selection_reason }}
                                            @else
                                                @foreach($keyword->embedding_results ?? [] as $embedding_result)
                                                    <a href="{{ rtrim($keyword->website->website_url, '/') }}{{ $embedding_result['url'] }}" target="_blank">
                                                        {{ $embedding_result['url'] }}
                                                    </a>
                                                    <span class="inline-flex items-center ml-2 px-1 py-1 bg-gray-200 hover:bg-gray-300 rounded-full text-sm font-semibold text-gray-600 text-xs" title="Embedding Score: {{ round($embedding_result['score'],2) }}">
                                                        {{ round($embedding_result['score'],2) }}
                                                    </span>
                                                    <br>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-500 sm:pl-6 text-wrap max-w-md">
                                            {{ $keyword->assignment_flag_notes }}
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
