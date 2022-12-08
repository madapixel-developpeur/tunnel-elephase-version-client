$(document).ready(function() {
    // $('input[type="file"]').each(function() {
    //     console.log($(this).attr('data-value'))
    //   $(this)[0].value = $(this).attr('data-value');
    // });
    // $(":file").filestyle({htmlIcon: '<i class="fa fa-file-image-o"></i>'});
    let stateIteration = 1;

    $(this).on('click','#add-images-details', function(e) {
        e.preventDefault();
        stateIteration += 1;
        $('.images-details-container').append(input_file_components(stateIteration));
        recompteIteration();
    })

    $(this).on('click', '.inputFileDelete', function(e) {
        e.preventDefault();
        stateIteration -= 1;
        // prendre les id des images supprimés
        var id = $(this).closest('.edit.input_file_components').find('input[type="hidden"]').val();
        if(id !== undefined) {
            const inputHidden = '<input type="hidden" name="images_deleted[]"  value="'+id+'"/>';
            $(this).closest('form').append(inputHidden);
        }
        $(this).closest('.input_file_components').remove();
        recompteIteration();

    })
})


function input_file_components(stateIteration)
{
    let row = $('<div />', {
        class:'row input_file_components'
    });
    let col_md_s = $('<div />', {
        class: 'col-md-11'
    })
    let col_md_e = $('<div />', {
        class: 'col-md-1 d-flex'
    })

    let form_widget = $('<div />', {
        class: 'my-form-widget'
    })
    let label_widget = $('<label />', {
        for: 'images_details_'+stateIteration,
        class:'label-input'
    })
    label_widget.html('image '+stateIteration);
    let inputFileModel = $('<input />', {
        name: 'images[]',
        type: 'file',
        class: 'form-control',
        id: 'images_details_'+stateIteration
    });
    let inputFileDelete = $('<button />', {
        class: 'btn btn-danger btn-sm ms-auto mt-auto mb-3 inputFileDelete',
        id: 'inputFileDelete_'+stateIteration
    }).html('<i class="fa fa-trash"></i>')
    form_widget.append(label_widget)
    form_widget.append(inputFileModel)
    col_md_s.append(form_widget)
    col_md_e.append(inputFileDelete)
    row.append(col_md_s)
    row.append(col_md_e)
    return row;
}

function recompteIteration()
{
    let new_iteration = 1;
    $('.input_file_components').each(function() {
        new_iteration ++;
        $(this).find('.label-input').html('image '+new_iteration)
    })
}
