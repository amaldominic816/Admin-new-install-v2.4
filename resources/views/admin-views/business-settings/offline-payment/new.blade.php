@extends('layouts.admin.app')
@section('title', translate('add_Offline_Payment_Method'))

@push('css_or_js')

@endpush

@section('content')
    <!-- Main Content -->

    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-0 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">

                {{translate('Add_Offline_Payment_Method')}}
            </h2>
        </div>

                    <form action="{{ route('admin.business-settings.offline.store') }}" method="POST">
                        @csrf
                        <div class="d-flex justify-content-end mb-3 mt-3">
                            <div class="text--primary-2 d-flex flex-wrap align-items-center " id="bkashInfoModalButton">
                                    {{ translate('Section_View') }}
                                <div class="ml-2 blinkings">
                                    <i class="tio-info-outined"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex  justify-content-between">
                                <div class="d-flex align-items-center gap-2">

                                    <img width="25" src="{{asset('/public/assets/admin/img/payment-card.png')}}" alt="">
                                    <h4 class="page-title mt-2">{{translate('payment_information')}}</h4>
                                </div>
                                <button class="btn btn--primary" id="add-more-field-payment">
                                    <i class="tio-add"></i> {{ translate('Add_New_Field') }}
                                </button>
                            </div>
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="form-group">
                                            <label for="method_name" class="title_color">{{ translate('payment_Method_Name') }}</label>
                                            <input type="text" class="form-control text-break" id="method_name" placeholder="{{ translate('ex:_bkash') }}" name="method_name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-3" id="custom-field-section-payment"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-3 mt-4">
                            <div class="d-flex gap-2 justify-content-end text-primary fw-bold" id="paymentInfoModalButton">
                                {{ translate('Section_View') }}
                                <div class="ml-2 blinkings">
                                    <i class="tio-info-outined"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2">
                                <img width="25" src="{{asset('/public/assets/admin/img/payment-card-fill.png')}}" alt="">
                                <h4 class="page-title mt-2">{{translate('Required Information from Customer')}}</h4>
                                </div>
                                <button class="btn btn--primary" id="add-more-field-customer">
                                    <i class="tio-add"></i> {{ translate('Add_New_Field') }}
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-xl-4 col-sm-6">
                                        <label for="payment_note">{{translate('Payment_Note')}} </label>
                                        <div class="form-floating">
                                            <textarea class="form-control" name="payment_note" id="payment_note"
                                                placeholder="{{ translate('Ex:ABC_Company') }}" value="" disabled></textarea>
                                        </div>
                                    </div>
                                </div>

                                    <div class="customer-input-fields-section" id="custom-field-section-customer"></div>
                            </div>
                        </div>

                        <!-- BUTTON -->
                        <div class="btn--container justify-content-end mt-3">
                            <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                            <button type="submit" class="btn btn--primary demo_check">{{translate('Submit')}}</button>
                        </div>
                    </form>
                </div>
            {{-- </div>
        </div> --}}

    <!-- End Main Content -->

    <!-- Section View Modal -->
    <div class="modal fade" id="sectionViewModal" tabindex="-1" aria-labelledby="sectionViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-end border-0">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
            <div class="modal-body">
            <div class="d-flex align-items-center flex-column gap-3 text-center">
                <h3>{{translate('Offline Payment')}}
                </h3>
                <img width="100" src="{{asset('public/assets/admin/img/offline_payment.png')}}" alt="">
                <p class="text-muted">{{translate('This view is from the user app.')}} <br class="d-none d-sm-block"> {{translate('This is how customer will see in the app')}}</p>
            </div>

            <div class="rounded p-4 mt-3" id="offline_payment_top_part">
                <div class="card border-primary">
                    <div class="card-body">
                <div class="d-flex justify-content-between gap-2 mb-3">
                    <h4 id="payment_modal_method_name"><span></span></h4>
                    <div class="text-primary d-flex align-items-center gap-2">
                        {{translate('Pay on this account')}}
                        <img width="25" src="{{asset('public/assets/admin/img/tick.png')}}" alt="">
                    </div>
                </div>

                <div class="d-flex text-wrap flex-column gap-2" id="methodNameDisplay"> </div>
                <div class="d-flex text-wrap flex-column gap-2" id="displayDataDiv"> </div>
            </div>
            </div>
        </div>

            <div class="rounded p-4 mt-3 mt-4" id="offline_payment_bottom_part">
                <h2 class="text-center mb-4">{{translate('Amount')}} : xxx</h2>

                <h4 class="mb-3">{{translate('Payment Info')}}</h4>
                <div class="d-flex flex-column gap-3 mb-3" id="customer-info-display-div">

                </div>
                <div class="d-flex flex-column gap-3">
                    <textarea name="payment_note" id="payment_note" class="form-control"
                        readonly rows="10" placeholder="Note"></textarea>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>



















@endsection

@push('script_2')

    <script>
        // Update the modal class based on the argument
        function openModal(contentArgument) {
            if (contentArgument === "bkashInfo") {
                $("#sectionViewModal #offline_payment_top_part").addClass("active");
                $("#sectionViewModal #offline_payment_bottom_part").removeClass("active");

                var methodName = $('#method_name').val();

                if (methodName !== '') {
                    $('#payment_modal_method_name').text(methodName + ' ' + 'Info');
                }

                function extractPaymentData() {
                var data = [];

                    $('.field-row-payment').each(function(index) {
                        console.log('modal')
                        var title = $(this).find('input[name="input_name[]"]').val();
                        var dataValue = $(this).find('input[name="input_data[]"]').val();
                        data.push({ title: title, data: dataValue });
                    });

                    return data;
                }

                var extractedData = extractPaymentData();


                function displayPaymentData() {
                    var displayDiv = $('#displayDataDiv');
                    var methodNameDisplay = $('#methodNameDisplay');
                    methodNameDisplay.empty();
                    displayDiv.empty();

                    var paymentElement = $('<span>').text('Payment Method');
                    var payementDataElement = $('<span>').html(methodName);

                    var dataRow = $('<div>').addClass('d-flex gap-3 align-items-center mb-2');
                    dataRow.append(paymentElement).append($('<span>').text(':')).append(payementDataElement);


                    methodNameDisplay.append(dataRow);

                    extractedData.forEach(function(item) {
                        var titleElement = $('<span>').text(item.title);
                        var dataElement = $('<span>').html(item.data);

                        var dataRow = $('<div>').addClass('d-flex gap-3 align-items-center');

                        if (item.title !== '') {
                            dataRow.append(titleElement).append($('<span>').text(':')).append(dataElement);
                            displayDiv.append(dataRow);
                        }

                    });
                }
                displayPaymentData();

                //customer info
                function extractCustomerData() {
                    var data = [];

                    $('.field-row-customer').each(function(index) {
                        var fieldName = $(this).find('input[name="customer_input[' + index + ']"]').val();
                        var placeholder = $(this).find('input[name="customer_placeholder[' + index + ']"]').val();
                        var isRequired = $(this).find('input[name="is_required[' + index + ']"]').prop('checked');
                        data.push({ fieldName: fieldName, placeholder: placeholder, isRequired: isRequired });
                    });

                    return data;
                }

                var extractedCustomerData = extractCustomerData();
                $('#customer-info-display-div').empty();

                // Loop through the extracted data and populate the display div
                $.each(extractedCustomerData, function(index, item) {
                    var isRequiredAttribute = item.isRequired ? 'required' : '';
                    var displayHtml = `
                        <input type="text" class="form-control mb-2" name="payment_by" readonly
                        id="payment_by" placeholder="${item.placeholder}"  ${isRequiredAttribute}>
                    `;
                    $('#customer-info-display-div').append(displayHtml);
                });

            } else {
                $("#sectionViewModal #offline_payment_top_part").removeClass("active");
                $("#sectionViewModal #offline_payment_bottom_part").addClass("active");

                var methodName = $('#method_name').val();

                if (methodName !== '') {
                    $('#payment_modal_method_name').text(methodName + ' ' + 'Info');
                }

                // $('.payment_modal_method_name').text(methodName);

                function extractPaymentData() {
                var data = [];

                    $('.field-row-payment').each(function(index) {
                        console.log('modal')
                        var title = $(this).find('input[name="input_name[]"]').val();
                        var dataValue = $(this).find('input[name="input_data[]"]').val();
                        data.push({ title: title, data: dataValue });
                    });

                    return data;
                }

                var extractedData = extractPaymentData();


                function displayPaymentData() {
                    var displayDiv = $('#displayDataDiv');
                    var methodNameDisplay = $('#methodNameDisplay');
                    methodNameDisplay.empty();
                    displayDiv.empty();

                    var paymentElement = $('<span>').text('Payment Method');
                    var payementDataElement = $('<span>').html(methodName);

                    var dataRow = $('<div>').addClass('d-flex gap-3 align-items-center mb-2');
                    dataRow.append(paymentElement).append($('<span>').text(':')).append(payementDataElement);


                    methodNameDisplay.append(dataRow);

                    extractedData.forEach(function(item) {
                        var titleElement = $('<span>').text(item.title);
                        var dataElement = $('<span>').html(item.data);

                        var dataRow = $('<div>').addClass('d-flex gap-3 align-items-center');

                        if (item.title !== '') {
                            dataRow.append(titleElement).append($('<span>').text(':')).append(dataElement);
                            displayDiv.append(dataRow);
                        }

                    });
                }
                displayPaymentData();

                //customer info
                function extractCustomerData() {
                    var data = [];

                    $('.field-row-customer').each(function(index) {
                        var fieldName = $(this).find('input[name="customer_input[' + index + ']"]').val();
                        var placeholder = $(this).find('input[name="customer_placeholder[' + index + ']"]').val();
                        var isRequired = $(this).find('input[name="is_required[' + index + ']"]').prop('checked');
                        data.push({ fieldName: fieldName, placeholder: placeholder, isRequired: isRequired });
                    });

                    return data;
                }

                var extractedCustomerData = extractCustomerData();
                $('#customer-info-display-div').empty();

                // Loop through the extracted data and populate the display div
                $.each(extractedCustomerData, function(index, item) {
                    var isRequiredAttribute = item.isRequired ? 'required' : '';
                    var displayHtml = `
                        <input type="text" class="form-control mb-2" name="payment_by" readonly
                            id="payment_by" placeholder="${item.placeholder}"  ${isRequiredAttribute}>
                    `;
                    $('#customer-info-display-div').append(displayHtml);
                });
            }

            // Open the modal
            $("#sectionViewModal").modal("show");
        }

        $(document).ready(function() {
            $("#bkashInfoModalButton").on('click', function() {
                console.log("something");
                var contentArgument = "bkashInfo";
                openModal(contentArgument);
            });
            $("#paymentInfoModalButton").on('click', function() {
                var contentArgument = "paymentInfo";
                openModal(contentArgument);
            });
        });
    </script>


    <script>
        function remove_field(fieldRowId) {
            $( `#field-row-customer--${fieldRowId}` ).remove();
            counter--;
        }

        function remove_field_payment(fieldRowId) {
            $( `#field-row-payment--${fieldRowId}` ).remove();
            counterPayment--;
        }

        jQuery(document).ready(function ($) {
            counter = 0;
            counterPayment = 0;

            $('#add-more-field-customer').on('click', function (event) {
                if(counter < 14) {
                    event.preventDefault();

                    $('#custom-field-section-customer').append(
                        `<div id="field-row-customer--${counter}" class="field-row-customer">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{translate('input_field_name')}} *</label>
                                        <input type="text" class="form-control" name="customer_input[${counter}]"
                                        placeholder="{{ translate('ex') }}: {{ translate('payment_By') }}" value="" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{translate('placeholder')}} *</label>
                                        <input type="text" class="form-control" name="customer_placeholder[${counter}]"
                                        placeholder="{{ translate('ex') }}: {{ translate('Enter Name') }}" value="" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between gap-2">
                                            <div class="form-check text-start mb-3">
                                            <input class="form-check-input" type="checkbox" value="1" name="is_required[${counter}]" id="flexCheckDefault__${counter}" checked>
                                            <label class="form-check-label" for="flexCheckDefault__${counter}">
                                                {{translate('is_required_?')}}
                                            </label>
                                        </div>
                                        <span class="btn action-btn btn--danger btn-outline-danger" onclick="remove_field(${counter})"  style="cursor: pointer;">
                                            <i class="tio-delete-outlined"></i>
                                        </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>`
                    );

                    $(".js-select").select2();

                    counter++;
                } else {
                    Swal.fire({
                        title: '{{translate('Reached maximum')}}',
                        confirmButtonText: '{{translate('ok')}}',
                    });
                }
            })

            $('#add-more-field-payment').on('click', function (event) {
                if(counterPayment < 14) {
                    event.preventDefault();

                    $('#custom-field-section-payment').append(
                        `<div id="field-row-payment--${counterPayment}" class="field-row-payment">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                <div class="form-group">
                                    <label class="title_color">{{ translate('Title') }}</label>
                                    <input type="text" name="input_name[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('Bank_Name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="input_data" class="title_color">{{ translate('Data') }}</label>
                                    <input type="text" name="input_data[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('ABC_bank') }}" required>
                                </div>
                            </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                    <div class="d-flex justify-content-end">
                                        <span class="btn action-btn btn--danger btn-outline-danger" onclick="remove_field_payment(${counterPayment})"  style="cursor: pointer;">
                                            <i class="tio-delete-outlined"></i>
                                        </span>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>`
                    );

                    $(".js-select").select2();

                    counterPayment++;
                } else {
                    Swal.fire({
                        title: '{{translate('Reached maximum')}}',
                        confirmButtonText: '{{translate('ok')}}',
                    });
                }
            })

            $('form').on('reset', function (event) {
                if(counter > 1) {
                    $('#custom-field-section-payment').html("");
                    $('#custom-field-section-customer').html("");
                    $('#method_name').val("");
                    $('#payment_note').val("");
                }

                counter = 1;
            })
        });
    </script>


@endpush
