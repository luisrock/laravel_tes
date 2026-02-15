@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="tw-flex tw-gap-2 tw-items-center tw-justify-between">

        @if ($paginator->onFirstPage())
            <span class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-600 tw-bg-white tw-border tw-border-gray-300 tw-cursor-not-allowed tw-leading-5 tw-rounded-md dark:tw-text-gray-300 dark:tw-bg-gray-700 dark:tw-border-gray-600">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-800 tw-bg-white tw-border tw-border-gray-300 tw-leading-5 tw-rounded-md hover:tw-text-gray-700 focus:tw-outline-none focus:tw-ring tw-ring-gray-300 focus:tw-border-blue-300 active:tw-bg-gray-100 active:tw-text-gray-800 tw-transition tw-ease-in-out tw-duration-150 dark:tw-bg-gray-800 dark:tw-border-gray-600 dark:tw-text-gray-200 dark:focus:tw-border-blue-700 dark:active:tw-bg-gray-700 dark:active:tw-text-gray-300 hover:tw-bg-gray-100 dark:hover:tw-bg-gray-900 dark:hover:tw-text-gray-200">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-800 tw-bg-white tw-border tw-border-gray-300 tw-leading-5 tw-rounded-md hover:tw-text-gray-700 focus:tw-outline-none focus:tw-ring tw-ring-gray-300 focus:tw-border-blue-300 active:tw-bg-gray-100 active:tw-text-gray-800 tw-transition tw-ease-in-out tw-duration-150 dark:tw-bg-gray-800 dark:tw-border-gray-600 dark:tw-text-gray-200 dark:focus:tw-border-blue-700 dark:active:tw-bg-gray-700 dark:active:tw-text-gray-300 hover:tw-bg-gray-100 dark:hover:tw-bg-gray-900 dark:hover:tw-text-gray-200">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-600 tw-bg-white tw-border tw-border-gray-300 tw-cursor-not-allowed tw-leading-5 tw-rounded-md dark:tw-text-gray-300 dark:tw-bg-gray-700 dark:tw-border-gray-600">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
