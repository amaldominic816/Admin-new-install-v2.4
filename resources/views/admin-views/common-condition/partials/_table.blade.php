@foreach($conditions as $key=>$condition)
<tr>
    <td>{{$key+1}}</td>
    <td>
        <span class="d-block font-size-sm text-body">
            {{Str::limit($condition['name'], 20,'...')}}
        </span>
    </td>
    <td>
        <span class="d-block font-size-sm text-body text-center">
            {{ $condition->items->count()}}
        </span>
    </td>
    <td>
        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$condition->id}}">
            <input type="checkbox" onclick="location.href='{{route('admin.common-condition.status',[$condition['id'],$condition->status?0:1])}}'"class="toggle-switch-input" id="stocksCheckbox{{$condition->id}}" {{$condition->status?'checked':''}}>
            <span class="toggle-switch-label mx-auto">
                <span class="toggle-switch-indicator"></span>
            </span>
        </label>
    </td>
    <td>
        <div class="btn--container justify-content-center">
            <a class="btn action-btn btn--primary btn-outline-primary"
                href="{{route('admin.common-condition.edit',[$condition['id']])}}" title="{{translate('messages.edit_condition')}}"><i class="tio-edit"></i>
            </a>
            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:"
            onclick="form_alert('condition-{{$condition['id']}}','Want to delete this condition')" title="{{translate('messages.delete_condition')}}"><i class="tio-delete-outlined"></i>
            </a>
            <form action="{{route('admin.common-condition.delete',[$condition['id']])}}" method="post" id="condition-{{$condition['id']}}">
                @csrf @method('delete')
            </form>
        </div>
    </td>
</tr>
@endforeach
