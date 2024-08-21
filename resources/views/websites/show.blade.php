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
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 text-center">Search Volume</th>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 text-center">Difficulty</th>
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
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                            {{ $keywordData->where('keyword', $keyword->keyword)->first()->search_volume ?? 0 }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                            {!! ($keywordData->where('keyword', $keyword->keyword)->first()->difficulty) ? str_replace('999', '<small>-</small>', $keywordData->where('keyword', $keyword->keyword)->first()->difficulty) : 0 !!}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <strong>AI:</strong> {{ $keyword->assigned_page }} <br>
                                            <small>
                                                Hike:  {{ str_replace(rtrim($website->website_url, '/'), '', $keyword->hike_assigned_page) }}
                                            </small>
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
                                                <a href="{{ rtrim($website->website_url, '/') }}{{ $embedding_result['url'] }}" target="_blank">
                                                    {{ $embedding_result['url'] }}
                                                </a>
                                                <span class="inline-flex items-center ml-2 px-1 py-1 bg-gray-200 hover:bg-gray-300 rounded-full text-sm font-semibold text-gray-600 text-xs" title="Embedding Score: {{ round($embedding_result['score'],2) }}">
                                                    {{ round($embedding_result['score'],2) }}
                                                </span>
                                                <br>
                                            @endforeach
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            @if($keyword->assignment_flagged)
                                                <a href="/keyword/unflag/{{ $keyword->id }}" class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-red-600 text-sm font-medium rounded-md">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                                    </svg>
                                                    <span class="ml-1">Remove Flag</span>
                                                </a>
                                            @else
                                                <a onclick="toggleModal(this)" data-modal="flag-keyword-modal" data-keyword_id="{{ $keyword->id }}" class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                                    </svg>
                                                    <span class="ml-1">Flag</span>
                                                </a>
                                            @endif
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

    <div class="px-4 sm:px-6 lg:px-8 mt-9">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Website Pages</h1>
            </div>
        </div>
        <div class="mt-2 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 text-center">Page URL</th>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 text-center">Page Title</th>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 text-center">Meta Description</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($crawl as $page)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ $page->url }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $page->title }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $page->meta_desc }}
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

    <div class="hidden overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center" id="flag-keyword-modal">
        <div class="relative w-auto my-6 mx-auto max-w-3xl">
            <!--content-->
            <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                <!--header-->
                <div class="flex items-start justify-between p-5 border-b border-solid border-blueGray-200 rounded-t">
                    <h3 class="text-3xl font-semibold">
                        Flag Keyword
                    </h3>
                    <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" onclick="toggleModal(this)" data-modal="flag-keyword-modal">
                        <span class="bg-transparent text-black h-6 w-6 text-2xl block outline-none focus:outline-none">
                        ×
                        </span>
                    </button>
                </div>
                <!--body-->
                <form action="/keyword/flag" method="post">
                    @csrf
                    <div class="relative p-6 flex-auto">
                        <div class="flex flex-wrap mb-6">
                            <div class="w-full px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="reason">
                                    Reason
                                </label>
                                <select id="reason" name="reason" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="">Incorrect Page - Not in embeddings</option>
                                    <option value="">Incorrect Page - Correct page in embeddings</option>
                                    <option value="">Keyword not selected</option>
                                </select>
                            </div>
                            <div class="w-full px-3 mb-6 mt-6 md:mb-0">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="notes">
                                    Notes
                                </label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full rounded-md border-0 py-1.5 px-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>
                    </div>
                    <!--footer-->
                    <div class="flex items-center justify-end p-6 border-t border-solid border-blueGray-200 rounded-b">
                        <a href="javascript:void(0)" class="text-red-500 background-transparent font-bold uppercase px-6 py-2 text-sm outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button" onclick="toggleModal(this)" data-modal="flag-keyword-modal">
                            Close
                        </a>
                        <input type="hidden" name="keyword" id="keywordHolder" value="">
                        <button class="bg-emerald-500 text-white active:bg-emerald-600 font-bold uppercase text-sm px-6 py-3 rounded shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="submit">
                            Flag Keyword
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="hidden opacity-25 fixed inset-0 z-40 bg-black" id="flag-keyword-modal-backdrop"></div>
    <script type="text/javascript">
        function toggleModal(element){
            console.log(element.getAttribute("data-keyword_id"));
            document.getElementById(element.getAttribute("data-modal")).classList.toggle("hidden");
            document.getElementById(element.getAttribute("data-modal") + "-backdrop").classList.toggle("hidden");
            document.getElementById(element.getAttribute("data-modal")).classList.toggle("flex");
            document.getElementById(element.getAttribute("data-modal") + "-backdrop").classList.toggle("flex");
            document.getElementById("keywordHolder").value = element.getAttribute("data-keyword_id");
        }
    </script>
@endsection
