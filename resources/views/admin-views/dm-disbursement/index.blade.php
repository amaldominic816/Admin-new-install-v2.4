@extends('layouts.admin.app')

@section('title',translate('messages.disbursement'))

@push('css_or_js')

@endpush

@section('content')


<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('/public/assets/admin/img/report/new/disburstment.png')}}" class="w--22" alt="">
            </span>
            <span>{{ translate('Deliveryman_Disbursement') }}</span>
        </h1>
        <ul class="nav nav-tabs mb-4 border-0 pt-2">
            <li class="nav-item">
                <a class="nav-link {{ $status == 'all'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'all']) }}" >{{ translate('all') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'pending'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'pending']) }}">{{ translate('pending') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'processing'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'processing']) }}">{{ translate('processing') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'completed'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'completed']) }}">{{ translate('completed') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'partially_completed'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'partially_completed']) }}">{{ translate('partially_completed') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'canceled'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'canceled']) }}">{{ translate('canceled') }}</a>
            </li>
        </ul>
    </div>
    <!-- Reports -->
    <div class="d-flex flex-column gap-2">
        @foreach($disbursements as $disbursement)
            <div class="card">
                <div class="card-header border-0 flex-wrap justify-content-between gap-4">
                    <div class="left">
                        <h3 class="m-0 font-bold">{{ $disbursement->title }}
                            @if($disbursement->status=='pending')
                                <label class="badge badge-soft-primary">{{ translate('pending') }}</label>
                            @elseif($disbursement->status=='completed')
                                <label class="badge badge-soft-success">{{ translate('Completed') }}</label>
                            @elseif($disbursement->status=='partially_completed')
                                <label class="badge badge-soft-info">{{ translate('partially_completed') }}</label>
                            @else
                                <label class="badge badge-soft-danger">{{ translate('canceled') }}</label>
                            @endif
                        </h3>
                        <span>{{ translate('created_at') }} {{ \App\CentralLogics\Helpers::time_date_format($disbursement->created_at) }}</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex flex-wrap align-items-center mr-2">
                            <span>{{ translate('total_amount') }}</span> <span class="mx-2">:</span> <h3 class="m-0">{{\App\CentralLogics\Helpers::format_currency($disbursement['total_amount'])}}</h3>
                        </div>
                        <div>
                            <a href="{{ route('admin.transactions.dm-disbursement.view', ['id' => $disbursement->id]) }}" class="btn btn--primary">{{ translate('view_details') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @if (count($disbursements) === 0)
          
                <div class="empty--data">
                     <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
            @endif
    </div>
    <div class="page-area px-4 pb-3">
        <div class="d-flex align-items-center justify-content-end">
            <div>
                {!!$disbursements->links()!!}
            </div>
        </div>
    </div>

</div>



@endsection

@push('script_2')

@endpush
