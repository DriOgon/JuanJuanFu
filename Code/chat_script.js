document.addEventListener('DOMContentLoaded', function() {
    // 获取输入框和文本显示部分的元素
    const userInput = document.getElementById('userInput');
    //const textDisplay = document.getElementById('textDisplay');
    const submitButton = document.getElementById('submitButton');

    // 添加点击事件监听器
    submitButton.addEventListener('click', function() {
        // 获取输入框的值
        const inputText = userInput.value;
        var message = send_post(inputText);
        // 更新文本显示部分的内容
        textDisplay.querySelector('p').textContent = "生成中ing";
        // 清空输入框
        userInput.value = '';
    });
});


function send_post(selectedText) {
    console.log("你好");
    if (($('#key').length) && ($('#key').val().length != 51)) {
        layer.msg("请输入正确的API-KEY", { icon: 5 });
        return;
    }

    // 将选中的文本作为问题
    var prompt =  "请你分析以下内容：\n"+selectedText;
    /*
    if (prompt == "") {
        layer.msg("请输入您的问题", { icon: 5 });
        return;
    }

    var loading = layer.msg('正在组织语言，请稍等片刻...', {
        icon: 16,
        shade: 0.4,
        time: false // 取消自动关闭
    });
    */

    // 发送请求到 setsession.php
    $.ajax({
        cache: true,
        type: "POST",
        url: "setsession.php",
        data: {
            message: prompt,
            context: '[]', // 如果需要上下文，可以在这里传递
            key: ($("#key").length) ? ($("#key").val()) : '',
        },
        dataType: "json",
        success: function (results) {
            // 调用 streaming 函数以获取 AI 回复
            streaming();
        }
    });

    function streaming() {
        var es = new EventSource("stream.php");
        var alltext = "";
        es.onmessage = function (event) {
            if (event.data == "[DONE]") {
                alert(alltext);
                const textDisplay = document.getElementById('textDisplay');
                textDisplay.querySelector('p').textContent = alltext; // 更新文本显示部分
                es.close();
                return alltext;
            }
            var json = eval("(" + event.data + ")");
            if (json.choices[0].delta.hasOwnProperty("content")) {
                if (alltext == "") {
                    alltext = json.choices[0].delta.content.replace(/^\n+/, ''); //去掉回复消息中偶尔开头就存在的连续换行符
                } else {
                    alltext += json.choices[0].delta.content;
                }
                
            }
        };

        es.onerror = function (event) {
            layer.close(loading);
            layer.msg("发生错误，请重试。");
            es.close();
        };
    }
}