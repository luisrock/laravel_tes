@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>

            </div>
        </div>
        
        <div class="col-md-12" style="margin-top:50px">
            <div class="card">
                <div class="card-header" style="display:flex; justify-content: space-between;">
                    Temas
                    <div>
                    <button id="toggle-forms">hide forms</button>
                    <button id="toggle-created">hide created</button>
                    </div>
                </div>

                <div class="card-body">

                <div class="block-content">
            <div class="table-responsive">
                <table class="table table-vcenter table-bordered">
                    <tbody>
                        <tr>
                        @foreach ($temas as $k => $t)
                        @php
                        $st_checked = '';
                        $lb_checked = 'check';
                        $st_created = '';
                        $lb_created = 'create';
                        $class_created = '';
                        $class_checked = '';
                        if($t->checked_at){
                            $st_checked = 'checked disabled';
                            $lb_checked = 'checked';
                            $st_created = 'disabled';
                            $class_checked = 'td-tema-checked';
                        }

                        if($t->created_at){
                            $st_created = 'checked disabled';
                            $lb_created = 'created';
                            $st_checked = 'disabled';
                            if(!empty($t->slug)) {
                                $class_created = 'td-tema-created';
                            }
                        }
                        @endphp
                            <td class="font-w600 font-size-sm td-tema {{ $class_created }} {{ $class_checked }}">
                                @if($class_created)
                                <a href="{{ route('temapage') . '/' . $t->slug }}">{{ $t->keyword }}</a>
                                @else
                                {{ $t->keyword }}
                                @endif
                                <code> ({{$t->results}})</code>
                                <span class="badge badge-pill badge-info toggle-form-tema" style="color: white; cursor:pointer;">action</span>
                                <span class="badge badge-pill del-form-tema" style="color: red;cursor: pointer;background-color: #fff;border: solid 1px red;" data-tema-id="{{ $t->id }}">del</span>
                                <form action="" class="form-tema" id="{{$k}}" data-tema-id="{{ $t->id }}">
                                    @csrf
                                    <div class="form-check" id="div-create-{{$k}}" style="color: green;">
                                        <input type="checkbox" class="form-check-input" name="create" id="create-{{$k}}" value="1" {{$st_created}}>
                                        <label class="form-check-label" for="create-{{$k}}">{{$lb_created}}</label>
                                    </div>
                                    <input class="form-control form-control-sm" type="text" id="inputLabel-{{$k}}" value="{{ $t->label ?? '' }}" style="display:none">
                                    <div class="form-check" id="div-check-{{$k}}" style="color: red;">
                                        <input type="checkbox" class="form-check-input" name="check" id="check-{{$k}}" value="1" {{$st_checked}}>
                                        <label class="form-check-label" for="check-{{$k}}">{{$lb_checked}}</label>
                                    </div>
                                    <input type="submit" value="go" class="btn-submit" style="float: right; display:none;">
                                </form>
                            </td>
                            @if(is_int(($k + 1)/3))
                        </tr>
                        <tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
                    
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('adminjs')
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script>

$( document ).ready(function() {

    //Primeira letra maiúsula, salvo se a palavra tiver menos de 3 caracteres
    function titleCase(str, limit = 3) {
        var splitStr = str.toLowerCase().split(' ');
        for (var i = 0; i < splitStr.length; i++) {
            if(splitStr[i].length < limit) {
                continue;
            }
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
        }
        // Directly return the joined string
        return splitStr.join(' '); 
    }

    $("#toggle-forms").on('click', function() {
        if($('.form-tema').is(":hidden")) {
            $('.form-tema').show()
            $('.del-form-tema').hide()
            $(this).text('hide forms')
        } else {
            $('.form-tema').hide()
            $('.del-form-tema').show()
            $(this).text('show forms')
        }
    });

    $("#toggle-created").on('click', function() {
        if($('.td-tema-created').is(":hidden")) {
            $('.td-tema-created').show()
            $(this).text('hide created')
        } else {
            $('.td-tema-created').hide()
            $(this).text('show created')
        }
    });

    $(".toggle-form-tema").on('click', function() {
        let form = $(this).siblings('form')
        form.parent().children('.del-form-tema').removeClass('warning')
            .removeClass('badge-danger')
            .removeAttr('style')
            .text('del')
            .css('color', 'red')
            .css('cursor', 'pointer')
            .css('background-color', '#fff')
            .css('border', 'solid 1px red');

        if(form.is(":hidden")) {
            form.show()
            $(this).siblings('.del-form-tema').hide()
        } else {
            form.hide()
            $(this).siblings('.del-form-tema').show()
        }
    });
    
    $("input[name='create']").change(function() {
        let form = $(this).parents('form')
        let id = form.attr('id')
        let btnSubmit = form.children('.btn-submit')
        let inputLabel = $("#inputLabel-" + id)
        let divCheck = $("#div-check-" + id)
        if(this.checked) {
            inputLabel.show()
            inputLabel.val(titleCase(form.parent().children('a').text().replace(/['"]+/g, '')));
            btnSubmit.show()
            divCheck.hide()
        } else{
            inputLabel.hide()
            btnSubmit.hide()
            divCheck.show()
        }
    });

    $("input[name='check']").change(function() {
        let form = $(this).parents('form')
        let id = form.attr('id')
        let btnSubmit = form.children('.btn-submit')
        let inputLabel = $("#inputLabel-" + id)
        let divCreate = $("#div-create-" + id)
        if(this.checked) {
            inputLabel.hide()
            divCreate.hide()
            btnSubmit.show()
        } else{
            // inputLabel.hide()
            divCreate.show()
            btnSubmit.hide()
            
        }
    });

    //Form Submission
    $('.form-tema').on('submit', function(e){
        e.preventDefault();
        let form = $(this);
        let formId = form.attr('id');
        let temaId = form.attr('data-tema-id');
        let inputLabel = $("#inputLabel-" + formId).val()
        let create = ($("#create-" + formId).prop("checked") == true) ? 1 : 0;
        let check = ($("#check-" + formId).prop("checked") == true) ? 1 : 0;
        let token = $("input[name='_token']").val();
        let data = {
            'id' : temaId,
            'label' : inputLabel,
            'create' : create,
            'check' : check,
            '_token' : token
        }
        form.children('.btn-submit').hide();

        $.ajax({
        url: "{{route('adminstore')}}",
        type:"POST",
        data: data,
        success:function(response){
            if(response.hasOwnProperty('success') && response['success'] == 1) {
                // $("#div-create-" + formId).show();
                // $("#div-check-" + formId).show();
                $("#create-" + formId).prop("disabled", true).parent().show();
                $("#check-" + formId).prop("disabled", true).parent().show();
                $("#inputLabel-" + formId).prop("disabled", true).show();
                if(!inputLabel) {
                    $("#inputLabel-" + formId).hide();
                }
            }
          //console.log(response);
        },
       });
    })


    //Del tema
    $('.del-form-tema').on('click', function(e){
        e.preventDefault();
        let btn = $(this);
        if(btn.hasClass('warning') === false) {
            btn.addClass('warning').addClass('badge-danger').removeAttr( 'style' ).css('color', '#fff').css('cursor', 'pointer');
            btn.text("Tem certeza?");
            return false;
        } 
        let siblingForm = btn.parent().children('form');
        let temaId = btn.attr('data-tema-id');
        let token = siblingForm.children("input[name='_token']").val();
        let data = {
            'id' : temaId,
            '_token' : token
        }
        siblingForm.hide();

        $.ajax({
        url: "{{route('admindel')}}",
        type:"POST",
        data: data,
        success:function(response){
            if(response.hasOwnProperty('success') && response['success'] == 1) {
                btn.parent().html(siblingForm.parent().children('a').text()).css("background-color", "#ff00004f");
            }
          //console.log(response);
        },
       });
    })




});

</script>
@endsection
