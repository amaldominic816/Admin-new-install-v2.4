<table id="columnSearchDatatable"
        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
        data-hs-datatables-options='{
        "order": [],
        "orderCellsTop": true,

        "entries": "#datatableEntries",
        "isResponsive": false,
        "isShowPaging": false,
        "pagination": "datatablePagination"
        }'>
    <thead class="thead-light">
        <tr>
            <th class="border-0">{{translate('sl')}}</th>
            <th class="border-0">{{translate('messages.bonus_title')}}</th>
            <th class="border-0">{{translate('messages.bonus_info')}}</th>
            <th class="border-0">{{translate('messages.bonus_amount')}}</th>
            <th class="border-0">{{translate('messages.started_on')}}</th>
            <th class="border-0">{{translate('messages.expires_on')}}</th>
            <th class="border-0">{{translate('messages.status')}}</th>
            <th class="border-0 text-center">{{translate('messages.action')}}</th>
        </tr>
    </thead>

    <tbody id="set-rows">
        @foreach($bonuses as $key=>$bonus)
        <tr>
            <td>{{$key+1}}</td>
            <td>
            <span class="d-block font-size-sm text-body">
            {{Str::limit($bonus['title'],15,'...')}}
            </span>
            </td>
            <td>{{ translate('messages.minimum_add_amount') }} -    {{\App\CentralLogics\Helpers::format_currency($bonus['minimum_add_amount'])}} <br>
                {{ translate('messages.maximum_bonus') }} - {{\App\CentralLogics\Helpers::format_currency($bonus['maximum_bonus_amount'])}}</td>
            <td>{{$bonus->bonus_type == 'amount'?\App\CentralLogics\Helpers::format_currency($bonus['bonus_amount']): $bonus['bonus_amount'].' (%)'}}</td>
            <td>{{$bonus['start_date']}}</td>
            <td>{{$bonus['end_date']}}</td>
            <td>
                <label class="toggle-switch toggle-switch-sm" for="bonusCheckbox{{$bonus->id}}">
                    <input type="checkbox" onclick="location.href='{{route('admin.users.customer.wallet.bonus.status',[$bonus['id'],$bonus->status?0:1])}}'" class="toggle-switch-input" id="bonusCheckbox{{$bonus->id}}" {{$bonus->status?'checked':''}}>
                    <span class="toggle-switch-label">
                        <span class="toggle-switch-indicator"></span>
                    </span>
                </label>
            </td>
            <td>
                <div class="btn--container justify-content-center">

                    <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.users.customer.wallet.bonus.update',[$bonus['id']])}}"title="{{translate('messages.edit_bonus')}}"><i class="tio-edit"></i>
                    </a>
                    <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:" onclick="form_alert('bonus-{{$bonus['id']}}','{{ translate('Want to delete this bonus ?') }}')" title="{{translate('messages.delete_bonus')}}"><i class="tio-delete-outlined"></i>
                    </a>
                    <form action="{{route('admin.users.customer.wallet.bonus.delete',[$bonus['id']])}}"
                    method="post" id="bonus-{{$bonus['id']}}">
                        @csrf @method('delete')
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<hr>
<table>
    <tfoot>

    </tfoot>
</table>
