// ID

function loadGemini(languageID, languageName, objectName, description, route) {

    console.log(description)

    let product_name = $('#input-name-' + languageID);
    let trimObject = $("#" + objectName + "-" + languageID);

    var parseRoute = route.replace("amp;", '');


    if (description == null) {
        alert(" قم بتفعيل الاضافة ");
    }
    var strReplece = description.replace(/\{.*?\}/, $(product_name).val());


    var objectLoading = (objectName === "input-description") ? $(trimObject).parent('div') : $(trimObject);


    console.log(trimObject)

    $(objectLoading).loading();


    $.ajax({
        url: parseRoute,
        type: 'post',
        data: {gemini_content: strReplece},
        dataType: 'json',
        success: function (json) {


            if (json.status === false) {
                alert(json.message);
                $(objectLoading).loading('stop');
                return;
            }


            $(objectLoading).loading('stop');
            let jsonVal = json.result;

            if (objectName === "input-description") {
                //$(trimObject).summernote('code', jsonVal);
                CKEDITOR.instances[$(trimObject).attr('id')].setData('');
                setTimeout(() => {
                    CKEDITOR.instances[$(trimObject).attr('id')].insertHtml(jsonVal)
                }, 300)
            } else {
                $(trimObject).val(jsonVal);
            }
        },
        error: function (request, error) {
            console.log("Request: " + error);
            $(objectLoading).loading('stop');
        }
    });
}


function loadGeminiStatus(languages, modelConfig, route) {


    const parseModelConfig = JSON.parse(modelConfig);
    const parseLanguages = JSON.parse(languages);
    console.log(parseModelConfig)

    $(document).ready(function () {


        for (const language in parseLanguages) {
            let languageID = parseLanguages[language].language_id;
            let languageName = parseLanguages[language].name;


            let description = parseModelConfig['input_description'][languageID];
            let meta_title = parseModelConfig['input_meta_title'][languageID];
            let meta_description = parseModelConfig['input_meta_description'][languageID];
            let meta_keyword = parseModelConfig['input_meta_keyword'][languageID];
            let tag = parseModelConfig['input_tag'][languageID];



            let btnStyle = "d-flex flex-row justify-content-center align-items-center input-group-addon btn btn-primary btn-sm fa fa-info-circle text-center"

            $(`#input-name-${languageID}`).parent('div').addClass('input-group').append($('<span/>')
                .attr('role', 'button')
                .addClass(btnStyle)
                .click(function () {
                    loadGemini(languageID, languageName, "input-description", description, route);
                }));


            $(`[name='product_description[${languageID}][name]'`).on("keyup", function (event) {
                let product_name = $(this).val();
                initHint($(`#input-name-${languageID}`), product_name,description);
                initHint($(`#input-meta-title-${languageID}`), product_name,meta_title);
                initHint($(`#input-meta-description-${languageID}`), product_name,meta_description);
                initHint($(`#input-meta-keyword-${languageID}`), product_name,meta_keyword);
                initHint($(`#input-tag-${languageID}`), product_name,tag);
            });



            $(`#input-meta-title-${languageID}`).parent('div').addClass('input-group').append($('<span/>')
                .attr('role', 'button')
                .addClass(btnStyle)
                .click(function () {
                    loadGemini(languageID, languageName, "input-meta-title", meta_title, route);
                }));

            $(`#input-meta-description-${languageID}`).parent('div').addClass('input-group').append($('<span/>')
                .attr('role', 'button')
                .addClass(btnStyle)
                .click(function () {
                    loadGemini(languageID, languageName, "input-meta-description", meta_description, route);
                }));


            $(`#input-meta-keyword-${languageID}`).parent('div').addClass('input-group').append($('<span/>')
                .attr('role', 'button')
                .addClass(btnStyle)
                .click(function () {
                    loadGemini(languageID, languageName, "input-meta-keyword", meta_keyword, route);
                }));


            $(`#input-tag-${languageID}`).parent('div').addClass('input-group tr').append($('<span/>')
                .attr('role', 'button')
                .addClass(btnStyle)
                .click(function () {
                    loadGemini(languageID, languageName, "input-tag", tag, route);
                }))




        }

    })

}



function initHint(btn,product_name,description) {
    let  strReplece = description.replace(/\{.*?\}/, product_name);
    $(btn).next('span').attr('title',strReplece);
}






