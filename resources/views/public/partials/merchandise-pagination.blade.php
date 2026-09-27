@if($paginator->hasPages())
    <nav class="merch-pagination" aria-label="Merchandise pages">
        <p>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>
        <ul>
            <li>
                @if($paginator->onFirstPage())
                    <span aria-disabled="true">← Previous</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Previous</a>
                @endif
            </li>
            @foreach($elements as $element)
                @if(is_string($element))
                    <li><span class="merch-pagination__ellipsis" aria-hidden="true">{{ $element }}</span></li>
                @else
                    @foreach($element as $page => $url)
                        <li>
                            @if($page === $paginator->currentPage())
                                <span aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
            <li>
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>
                @else
                    <span aria-disabled="true">Next →</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
