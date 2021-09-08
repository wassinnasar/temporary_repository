@extends('admin.layout')

@section('content')
    <div class="container" style=" width: 95%">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Актуальная версия - {{ $version }}  </div>

                    <div class="card-header">Количество пользователей с актуальной версией - {{ $count }}  </div>

                    <style>
                        table td {
                            padding: 20px;
                            vertical-align: top
                        }

                        #chart_div, #form {
                            margin: 0px auto;
                            display: block;
                            width: 95%
                        }

                        #chart_div_AnnotationChart_legendContainer, #chart_div_AnnotationChart_zoomControlContainer {
                            font-size: 20px;
                        }

                        #chart_div_AnnotationChart_zoomControlContainer button {
                            font-size: 12px;
                            padding: 5px 10px
                        }


                        #chart_div_AnnotationChart_legendContainer span {
                            vertical-align: middle;
                            position: relative;
                            padding-left: 10px;
                        }

                        #chart_div_AnnotationChart_legendContainer span .legend-dot {
                            position: absolute;
                            left: 0px;
                            top: 50%;
                            margin-top: -4px;
                        }
                    </style>

                    <form id="form">
                        <table>
                            <tr>
                                <td>
                                    <a href="{{ route('admin.home') }}">Все</a>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" name="filter"
                                               value="en" <?php if (isset($_GET['filter'])) if ($_GET['filter'] == 'en') echo 'checked'; ?>
                                               onclick="this.form.submit();">
                                        Английский
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" name="filter"
                                               value="ru" <?php if (isset($_GET['filter'])) if ($_GET['filter'] == 'ru') echo 'checked'; ?>
                                               onclick="this.form.submit();">
                                        Русский
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </form>

{{--                    {{ dd($datas) }}--}}
                    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                    <script type='text/javascript'>
                        google.charts.load('current', {'packages': ['annotationchart']});
                        google.charts.setOnLoadCallback(drawChart);

                        function drawChart() {
                            var data = new google.visualization.DataTable();
                            data.addColumn('date', 'Date');
                            data.addColumn('number', 'Actual');
                            data.addColumn('number', 'Mobile');
                            data.addColumn('number', 'Indefinite');
                            data.addColumn('number', 'UniqueID');
                            data.addColumn('number', 'Definite Actual');
                            data.addColumn('number', 'Definite Mobile');

                            data.addRows([

                                <!--                            --><?php if ($datas) {
                                                            foreach ($datas as $dd) {
                                                                echo '
                                		  [new Date(' . $dd['realdata'] . '), ' . $dd['act'] . ', ' . $dd['unact'] . ', ' . $dd['undef'] . ', ' . $dd['uniqueid'] . ', ' . $dd['defunact'] . ', ' . $dd['defact'] . '],' . "\r\n";

                                                            }
                                                        }
                                                            ?>

                            ]);

                            var chart = new google.visualization.AnnotationChart(document.getElementById('chart_div'));
                            var date = new Date();
                            var day= date.getDate();
                            var end_month= date.getMonth();
                            var year= date.getFullYear();
                            var options = {
                                displayAnnotations: false,
                                zoomEndTime: new Date(year, end_month , day, 0, 0, 0, 0),
                                zoomStartTime: new Date(year, end_month - 1, day, 0, 0, 0, 0),
                            };

                            chart.draw(data, options);
                        }

                        // $( "#chart_div_AnnotationChart_zoomControlContainer_1-month" ).on( "click", notify );
                    </script>

                    <div id='chart_div' style='height: 500px;'></div>
                </div>
            </div>
        </div>

            <div class="card-header">Отчёты
            </div>

            <ul class="nav nav-pills nav-fill">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.logger') }}">Все логи</a>
                </li>
                <li class="last_visit nav-item">
                    <div class="nav-link"  style="color:#3490dc;cursor: pointer;" onClick="ShowTopTen()">ТОП 10</div>
                </li>
                <li class="topten none nav-item">
                    <div class="nav-link" style="color:#3490dc;cursor: pointer;" onClick="ShowLastVisit()">Последние посещения</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.city_requests') }}">Запросы городов</a>
                </li>
            </ul>
            @if(isset($hits))
            <div class="last_visit">
                <div class="card-header">Последние посещения
                </div>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">IP</th>
                        <th scope="col">Request</th>
                        <th scope="col">Время</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($hits as $item)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ $item->ip }}</td>
                                <td>{{ $item->log }}</td>
                                <td>{{ $item->created_at->addHours(3)->format('d-m-Y H:i:s')   }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            @if(isset($topten))
            <div class="topten none">
                <div class="card-header">Горячая Десятка!
                </div>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">id</th>
                        <th scope="col">uuid</th>
                        <th scope="col">last ip</th>
                        <th scope="col">count</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($topten as $item)
                        <tr>
                            {{--                            <th scope="row">{{ $loop->iteration }}</th>--}}
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->uuid }}</td>
                            <td>{{ $item->hits->first()->ip }}</td>
                            <td>{{ $item->hits->count() }}(+{{ $item->hits()->whereDate('created_at', Carbon\Carbon::today())->count() }})</td>
                        </tr>
                    @endforeach
                    </tbody>
                    </table>
                </div>
            @endif
        <script>
            function ShowTopTen() {
                $(".topten").removeClass("none");
                $(".last_visit").addClass("none");
            }
            function ShowLastVisit() {
                $(".last_visit").removeClass("none");
                $(".topten").addClass("none");
            }
        </script>
    </div>
@endsection
