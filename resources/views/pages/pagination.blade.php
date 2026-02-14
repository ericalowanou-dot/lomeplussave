@extends('layouts.app2')

@section('title', 'Articles')

@section('content')
<div class="container py-5">
    <h1 class="h4 mb-4">Articles</h1>
    @include('partials.articles-list', ['articles' => $articles])
    <div class="d-flex justify-content-center mt-4">
        {{ $articles->links() }}
    </div>
</div>
@endsection
