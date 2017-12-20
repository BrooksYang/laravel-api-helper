@extends('api_doc::layouts.app')

@section('css')
    <style>
        span {  width:60px; margin-right: 5px; }
        td a { color: #363636; }
    </style>
@endsection

@section('content')
    <table class="table is-fullwidth">
        <thead>
        <tr>
            <th>序号</th>
            <th>URI</th>
            <th>名称</th>
            <th>吞吐率</th>
            <th>模块</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($items as $key => $item)
            <tr>
                <th>{{ $key + 1 }}</th>
                <td>
                    {{-- method --}}
                    @if ($item['method'] == 'GET')
                        <span class="tag is-rounded is-info">{{ $item['method'] }}</span>
                    @elseif ($item['method'] == 'POST')
                        <span class="tag is-rounded is-success">{{ $item['method'] }}</span>
                    @elseif (in_array($item['method'], ['PUT', 'PATCH']))
                        <span class="tag is-rounded is-warning">{{ $item['method'] }}</span>
                    @elseif ($item['method'] == 'DELETE')
                        <span class="tag is-rounded is-danger">{{ $item['method'] }}</span>
                    @endif

                    {{-- URI --}}
                    <a href="{{ url('api/docs/' . $item['module'] . '/' . base64_encode(json_encode($item)) ) }}">
                        {{ $item['uri'] }}
                    </a>
                </td>

                <td>
                    {{-- name --}}
                    <a href="{{ url('api/docs/' . $item['module'] . '/' . base64_encode(json_encode($item)) ) }}">
                        {{ $item['name'] }}
                    </a>
                </td>
                <td>
                    <span class="tag is-light is-rounded">{{ @$item['last_server_test_result']['requests_per_second'] }}</span>
                </td>
                <td>{{ $item['module'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection