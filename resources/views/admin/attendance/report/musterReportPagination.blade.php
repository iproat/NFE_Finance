<style>
    table {
        margin: 0 0 40px 0;
        width: 100%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        display: table;
        border-collapse: collapse;
    }

    .printHead {
        width: 35%;
        margin: 0 auto;
    }

    table,
    td,
    th {
        font-size: 10px;
        border: 1px solid black;
    }

    td {
        font-size: 8px;
        padding: 3px;
    }

    th {
        padding: 3px;
    }

    .present {
        color: #7ace4c;
        font-weight: 700;
    }

    .absence {
        color: #f33155;
        font-weight: 700;
    }

    .leave {
        color: #41b3f9;
        font-weight: 700;
    }

    .bolt {
        font-weight: 700;
    }
</style>

<body style="word-wrap: break-word; font-family: Arial, sans-serif;">
    <div class="printHead" style="text-align: center;">
        <h3 style="margin-top: 10px;"><b>ProPeople - Muster Report</b></h3>
    </div>

    <div class="table-responsive">
        <table id="myDataTableAlter" class="table table-bordered table-striped table-hover"
            style="font-size: 14px; width: 100%;">
            <thead>
                <tr>
                    <td colspan="{{ count($monthToDate) + 4 }}" class="text-center">
                        <h2>Muster Report - {{ $start_date }} to {{ $end_date }}</h2>
                    </td>
                </tr>
                <tr>
                    <th>@lang('common.serial')</th>
                    <th>@lang('common.name')</th>
                    <th>@lang('common.department')</th>
                    <th>Title</th>
                    @foreach ($monthToDate as $head)
                        <th>{{ $head['day'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if (count($results) > 0)
                    @php $sl = null; @endphp

                    @foreach ($results as $fingerID => $attendance)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $attendance[0]['fullName'] }}</td>
                            <td>{{ $attendance[0]['department_name'] }}</td>
                            <td class="text-left">
                                Shift Name <br>
                                In Time <br>
                                Out Time <br>
                                Working Hrs <br>
                            </td>

                            @foreach ($attendance as $data)
                                @if (strtotime($data['date']) <= strtotime(date('Y-m-d')))
                                    <td>
                                        {{ $data['shift_name'] ?? 'NA' }}<br>
                                        {{ $data['in_time'] ? date('H:i', strtotime($data['in_time'])) : '-:-' }}<br>
                                        {{ $data['out_time'] ? date('H:i', strtotime($data['out_time'])) : '-:-' }}<br>
                                        {{ $data['working_time'] ? date('H:i', strtotime($data['working_time'])) : '-:-' }}<br>
                                    </td>
                                @else
                                    <td></td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="{{ count($monthToDate) + 4 }}">No data found...</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>
