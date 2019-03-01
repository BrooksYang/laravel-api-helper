@extends('api_helper::layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/api_helper/css/api-helper.css') }}">
@endsection

@section('content')
    <form action="{{ url('api/send') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}

        {{-- request info--}}
        <input type="hidden" name="methodForApiDoc" value="{{ $info['method'] }}">
        <input type="hidden" name="uriForApiDoc" value="{{ $info['uri'] }}">

        {{-- Button --}}
        <div class="">
            <a href="{{ url("api/docs/{$group}/{$module}") }}" class="button button-custom">接口列表</a>
            <button class="button is-outlined is-primary button-custom" type="submit">测试</button>
            @if ($pressureTest)
                <button class="button is-outlined is-danger button-custom" type="submit" onclick="serverTest()">压测</button>
            @endif
        </div>

        <div class="box">
            <p>
                <span class="tag is-rounded is-primary">HTTP</span><strong>{{ $info['name'] }}</strong>
                <br>

                {{-- method --}}
                @if ($info['method'] == 'GET')
                    <span class="tag is-rounded is-info">{{ $info['method'] }}</span>
                @elseif ($info['method'] == 'POST')
                    <span class="tag is-rounded is-success">{{ $info['method'] }}</span>
                @elseif (in_array($info['method'], ['PUT', 'PATCH']))
                    <span class="tag is-rounded is-warning">{{ $info['method'] }}</span>
                @elseif ($info['method'] == 'DELETE')
                    <span class="tag is-rounded is-danger">{{ $info['method'] }}</span>
                @endif
                {{ $info['uri'] }}
            </p>
        </div>

        <div class="box">
            <strong>请求头部：</strong>
            <hr>
            <div class="field has-addons">
                <p class="control">
                <div class="select">
                    <select>
                        <option>Authorization: Bearer</option>
                    </select>
                </div>
                </p>
                <p class="control is-expanded">
                    <input class="input" type="text" name="tokenForApiDoc" value="{{ session('tokenForApiDoc') }}" placeholder="请输入token">
                </p>
            </div>
        </div>

        @if (count($params))
            <div class="box">
                <strong>请求参数：</strong>
                <hr>
                <table class="table is-fullwidth is-narrow">
                    <thead>
                    <tr>
                        <th>参数</th>
                        <th>值</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($params as $key => $param)
                        <tr>
                            <td>{{ $param['param'] }}</td>
                            <td>
                                @if (isset($param['is_file']))
                                    <div class="file has-name is-fullwidth">
                                        <label class="file-label">
                                            <input class="file-input" type="file" name="{{ $param['param'] }}"
                                                   id="beingUploadFileInput" onchange="handleFileName(this.value)">
                                            <div class="file-cta">
                                                <div class="file-label">选择文件</div>
                                            </div>
                                            <div class="file-name" id="beingUploadFilename"></div>
                                        </label>
                                    </div>
                                @else
                                    <input class="input" type="text" name="{{ $param['param'] }}"
                                           value="{{ old($param['param']) }}"
                                           placeholder="{{ $param['comment'] }}">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- 压力测试 --}}
        @if ($pressureTest)
            <div class="box">
                <strong>压力测试：</strong>
                <hr>
                <div class="columns">
                    <div class="column">
                        <input class="input" type="text" id="total_requests_input" value="{{ old('total_requests') }}" placeholder="总请求数，默认100">
                    </div>
                    <div class="column">
                        <input class="input" type="text" id="concurrency_input" value="{{ old('concurrency') }}" placeholder="并发量，默认10">
                    </div>
                </div>
            </div>
        @endif

        {{-- 上次压测结果 --}}
        @if ($pressureTest && @$lastServerTestResult['requests_per_second'])
            <div class="box">
                <strong>上次压测结果：</strong>
                <hr>
                <table class="table is-fullwidth is-narrow">
                    <tbody>
                    <tr>
                        <td>Requests per second</td>
                        <td>吞吐率</td>
                        <td>
                            <span class="tag is-light is-rounded">{{ $lastServerTestResult['requests_per_second'] }}</span>
                            [#/sec] (mean)
                        </td>
                    </tr>
                    <tr>
                        <td>Time per request</td>
                        <td>用户平均请求等待时间</td>
                        <td>
                            <span class="tag is-light is-rounded">{{ $lastServerTestResult['time_per_request'] }}</span>
                            [ms] (mean)
                        </td>
                    </tr>
                    <tr>
                        <td>Time per request</td>
                        <td>服务器平均请求等待时间</td>
                        <td>
                            <span class="tag is-light is-rounded">{{ $lastServerTestResult['time_per_request_concurrent'] }}</span>
                            [ms] (mean, across all concurrent requests)
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- 返回结果 --}}
        <div class="box">
            <div class="columns">
                <div class="column">
                    <strong>返回结果：</strong>
                </div>

                {{-- Http Status--}}
                <div class="column">
                    @if (session('api_helper.response'))
                        <div class="level-right">
                            Status:
                            {{ session('api_helper.response.status') }}
                        </div>
                    @endif
                </div>
            </div>

            <hr>

            {{-- Response --}}
            <div class="content">

                {{-- 压力测试结果 --}}
                @if ($pressureTest && session('api_helper.response.status') == 200 && session('api_helper.pressure_test.command'))
                    <pre>{{ session('api_helper.pressure_test.command') }}<hr>{{ session('api_helper.pressure_test.report') }}</pre>
                    <hr>
                @endif

                {{-- Buttons --}}
                @if (session('api_helper.response'))
                    <div style="margin-bottom: 15px;">
                        <span class="button is-primary is-small" id="button_pretty" onclick="formatResponse('pretty')">
                            Pretty
                        </span>

                        <span class="button is-small" id="button_raw" onclick="formatResponse('raw')">
                            Raw
                        </span>

                        <span class="button is-small" id="button_preview" onclick="formatResponse('preview')">
                            Preview
                        </span>
                    </div>
                @endif

                {{-- Pretty --}}
                <div id="pretty">
                    <pre><span id="code">{{ session('api_helper.response.content') }}</span></pre>
                </div>

                {{-- Raw --}}
                <div id="raw" style="display: none;">
                    <pre>{{ session('api_helper.response.content') }}</pre>
                </div>

                {{-- Preview --}}
                <div id="preview" style="display: none;">
                    <span>{!! session('api_helper.response.content') !!}</span>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('Js')
    <script src="{{ asset('vendor/api_helper/js/api-helper.js') }}"></script>
@endsection
