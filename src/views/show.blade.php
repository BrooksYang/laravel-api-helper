@extends('api_doc::layouts.app')

@section('css')
    <style>
        span {
            width: 60px;
            margin-right: 5px;
        }

        .button-custom {
            width: 100px;
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('content')
    <form action="{{ url('api/send') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}

        {{-- request info--}}
        <input type="hidden" name="methodForApiDoc" value="{{ $info['method'] }}">
        <input type="hidden" name="uriForApiDoc" value="{{ $info['uri'] }}">

        {{-- Button --}}
        <div class="">
            <a href="{{ url("api/docs/$module") }}" class="button button-custom">接口列表</a>
            <button class="button is-outlined is-primary button-custom" type="submit">测试</button>
            <button class="button is-outlined is-danger button-custom" type="submit" onclick="serverTest()">压测</button>
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
        <div class="box">
            <strong>压力测试：</strong>
            <hr>
            <table class="table is-fullwidth is-narrow">
                <tbody>
                <tr>
                    <td>总请求数</td>
                    <td>
                        <input class="input" type="text" id="total_requests_input" value="{{ old('total_requests') }}" placeholder="总请求数，默认100">
                    </td>
                </tr>
                <tr>
                    <td>并发量</td>
                    <td>
                        <input class="input" type="text" id="concurrency_input" value="{{ old('concurrency') }}" placeholder="并发量，默认10">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        {{-- 上次压测结果 --}}
        @if (@$lastServerTestResult['requests_per_second'])
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
            <strong>返回结果：</strong>
            <hr>
            <pre><div id="code">{{ session('params') }}</div></pre>
        </div>

        {{-- 压力测试结果 --}}
        @if (session('response.command'))
            <div class="box">
                <strong>压测结果：</strong>
                <hr>
                <div class="content">
                    <p class="notification">{{ session('response.command') }}</p>
                    <pre>{{ session('response.report') }}</pre>
                </div>
            </div>
        @endif
    </form>
@endsection

@section('Js')
    <script>

        /**
         * 压力文件上传显示文件名测试
         */
        function handleFileName(filename) {
            let fileName = document.getElementById("beingUploadFilename");
            fileName.innerHTML = filename;
        }

        // 格式化json
        if (window["JSON"] && JSON["stringify"]) {
            let code = document.getElementById("code");
            if (code.innerHTML) {
                code.innerHTML = JSON.stringify(JSON.parse(code.innerHTML), undefined, 2);
            }
        }

        /**
         * 压力测试
         */
        function serverTest() {
            document.getElementById('total_requests_input').setAttribute('name', 'total_requests');
            document.getElementById('concurrency_input').setAttribute('name', 'concurrency');
        }
    </script>
@endsection
