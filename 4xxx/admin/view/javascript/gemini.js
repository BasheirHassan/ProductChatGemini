



function loadGemini(languageID, languageName, objectName, description, route) {
    if (!description) {
        alert(messages.help_active_extension);
        return;
    }

    const productNameInput = $(`#input-name-${languageID}`);
    const targetObject = $(`#${objectName}-${languageID}`);
    const parseRoute = route.replace("amp;", '');
    const descriptionReplaced = description.replace(/\{.*?\}/, productNameInput.val());
    const loadingTarget = (objectName === "input-description") ? targetObject.parent('div') : targetObject;

    if (!productNameInput.val()) {
        alert(messages.error_content);
        return;
    }

    $(loadingTarget).loading();

    $.ajax({
        url: parseRoute,
        type: 'POST',
        data: { gemini_content: descriptionReplaced },
        dataType: 'json',
        success: function (response) {
            $(loadingTarget).loading('stop');
            if (!response.status) {
                alert(response.message);
                return;
            }

            const resultContent = response.result;
            if (objectName === "input-description") {
                const editorInstance = CKEDITOR.instances[targetObject.attr('id')];
                editorInstance.setData('');
                setTimeout(() => editorInstance.insertHtml(resultContent), 300);
            } else {
                targetObject.val(resultContent);
            }
        },
        error: function (xhr, error) {
            console.error(`Request Error: ${error}`);
            $(loadingTarget).loading('stop');
        }
    });
}

function loadGeminiStatus(languages, modelConfig, route) {
    const languageConfigs = JSON.parse(languages);
    const modelConfigs = JSON.parse(modelConfig);


    // console.log(modelConfigs)

    $(document).ready(function () {
        for (const language in languageConfigs) {

            let languageID = languageConfigs[language].language_id;
            let languageName = languageConfigs[language].name;


            let description = modelConfigs.input_description[languageID];
            let metaTitle = modelConfigs.input_meta_title[languageID];
            let metaDescription = modelConfigs.input_meta_description[languageID];
            let metaKeyword = modelConfigs.input_meta_keyword[languageID];
            let tag = modelConfigs.input_tag[languageID];

            let val = $(`#input-name-${languageID}`).val();
            createInputEventHandlers(languageID, description, metaTitle, metaDescription, metaKeyword, tag);

            createHintButton(`#input-name-${languageID}`, description ,val,() => loadGemini(languageID, languageName, "input-description", description, route));
            createHintButton(`#input-meta-title-${languageID}`,metaTitle ,val,() => loadGemini(languageID, languageName, "input-meta-title", metaTitle, route));
            createHintButton(`#input-meta-description-${languageID}`,metaDescription,val, () => loadGemini(languageID, languageName, "input-meta-description", metaDescription, route));
            createHintButton(`#input-meta-keyword-${languageID}`,metaKeyword,val, () => loadGemini(languageID, languageName, "input-meta-keyword", metaKeyword, route));
            createHintButton(`#input-tag-${languageID}`,tag,val, () => loadGemini(languageID, languageName, "input-tag", tag, route));
        }
    });
}

function createHintButton(selector,title,inpuName, onClickHandler) {
    const btnStyle = "d-flex flex-row justify-content-center align-items-center input-group-addon btn btn-primary btn-sm fa fa-info-circle text-center chat-gemini-btn";
    const newTitle = replaceBracesText(title, `[${inpuName}]`);

    $(selector).parent('div').addClass('input-group').append(
        $('<span/>')
            .attr('role', 'button')
            .attr('title', newTitle)
            .addClass(btnStyle)
            .click(onClickHandler)
    );
}

function replaceBracesText(str, replacement) {
    return  str.replace(/\{.*?\}/g, replacement);
}


function createInputEventHandlers(languageID, description, metaTitle, metaDescription, metaKeyword, tag) {
    const nameInputSelector = `[name='product_description[${languageID}][name]'`;
    $(nameInputSelector).on("keyup", function () {
        const productName = "["+$(this).val()+"]";
        updateHint(`#input-name-${languageID}`, productName, description);
        updateHint(`#input-meta-title-${languageID}`, productName, metaTitle);
        updateHint(`#input-meta-description-${languageID}`, productName, metaDescription);
        updateHint(`#input-meta-keyword-${languageID}`, productName, metaKeyword);
        updateHint(`#input-tag-${languageID}`, productName, tag);
    });
}

function updateHint(selector, productName, description) {
    const updatedDescription = description.replace(/\{.*?\}/, productName);
    $(selector).next('span').attr('title', updatedDescription);
}


function getGeminiAll() {
    $(".chat-gemini-btn").click();
}




function testApi() {
    const apiKey = $("#input-api_key").val().trim(); if (!apiKey) { return; }

    let apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${apiKey}`;
    let requestData = {
        contents: [{ role: "user", parts: [{ text: 'test Api' }] }]
    };

    $('#module_product_chat_gemini_select_model').loading('start');

    $.ajax({
        url: apiUrl,
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(requestData),
        success: function(response) {
            $('#module_product_chat_gemini_select_model').loading('stop');
            $('#alertX').html('Successful!').removeClass('text-danger').addClass('text-success');
            listModels(apiKey);
        },
        error: function(xhr, status, error) {
            // alert(xhr.responseJSON.error.message);
            $('#alertX').html(xhr.responseJSON.error.message).removeClass('text-success').addClass('text-danger');
            $('#alertX').loading('stop');
            setTimeout(()=>{
                $('#alertX').html('');
            },5000)
        }
    })

}