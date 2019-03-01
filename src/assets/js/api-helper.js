/**
 * 压力文件上传显示文件名测试
 */
function handleFileName(filename) {
    let fileName = document.getElementById('beingUploadFilename');
    fileName.innerHTML = filename;
}

/**
 * 格式化相应数据
 */
function formatResponse(type = 'pretty') {
    let pretty = document.getElementById('pretty');
    let raw = document.getElementById('raw');
    let preview = document.getElementById('preview');
    
    let prettyButton = document.getElementById('button_pretty');
    let rawButton = document.getElementById('button_raw');
    let previewButton = document.getElementById('button_preview');
    
    if (type == 'pretty') {
        // response
        pretty.style.display = 'block';
        raw.style.display = 'none';
        preview.style.display = 'none';
        
        // button
        prettyButton.classList.add('is-primary');
        rawButton.classList.remove('is-primary');
        previewButton.classList.remove('is-primary');
    }
    
    if (type == 'raw') {
        // response
        pretty.style.display = 'none';
        raw.style.display = 'block';
        preview.style.display = 'none';
        
        // button
        prettyButton.classList.remove('is-primary');
        rawButton.classList.add('is-primary');
        previewButton.classList.remove('is-primary');
    }
    
    if (type == 'preview') {
        // response
        pretty.style.display = 'none';
        raw.style.display = 'none';
        preview.style.display = 'block';
        
        // button
        prettyButton.classList.remove('is-primary');
        rawButton.classList.remove('is-primary');
        previewButton.classList.add('is-primary');
    }
}

// 格式化json
if (window['JSON'] && JSON['stringify']) {
    let code = document.getElementById('code');
    if (code.innerHTML) {
        code.innerHTML = JSON.stringify(JSON.parse(code.innerHTML), undefined, 2);
    }
}

/**
 * 压力测试
 */
function serverTest() {
    document.getElementById('total_requests_input').setAttribute('name', 'total_requests');
    document.getElementById('concurrency_input').setAttribute('name', 'concurrency');
}
