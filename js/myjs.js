$(function(){
    let rally_array = []; //ラリー配列
    let service_flag = true; //サービス権 true:分析選手，false: 対戦相手
    let court_flag = true; //コート true: 分析選手上，false: 分析選手下
    var game_title = '未登録'; //試合名
    var opponent_name = '未登録'; //対戦相手氏名
    var opponent_affiliation = '未登録'; //対戦相手所属
    var court_loc = true; //true: 上部，false: 下部
    var result_text = '';
    //試合名が入力された時に発火
    $('#game_title').change(function(){
        game_title = $(this).val();
        showServicePosition();
    })

    //対戦相手の名前が入力された時に発火
    $('#opponent_name').change(function(){
        opponent_name = $(this).val();
        showServicePosition();
    })

    //対戦相手の所属が入力された時に発火
    $('#opponent_affiliation').change(function(){
        opponent_affiliation = $(this).val();
    })

    //サービスチェンジ
    $('#serviceChange').on("click", function(event){
        //サービスフラグ交代
        service_flag = !service_flag;
        //最初に触るコートのメモ (上部か下部)
        court_loc = !court_loc;
        showServicePosition();
    });

    //コートチェンジ
    $('#courtChange').on("click", function(event){
        court_flag = !court_flag;
        showServicePosition();
    });

    //コートボタンをクリックした場合に発火
    $('.court').on("click", function(event){
        //ラリーが開始された場合
        //サービス交代とコート交代を使用不可にする
        $('#serviceChange').prop("disabled", true);
        $('#courtChange').prop("disabled", true);
        $('#reset_bt').prop("disabled", false);

        //上か下のコートのどちらを押したか
        if ($(this).hasClass('upper_bt')) { //上
            $('.upper_bt').prop("disabled", true);
            $('.bottom_bt').prop("disabled", false);
        }else{ //下
            $('.upper_bt').prop("disabled", false);
            $('.bottom_bt').prop("disabled", true);
        }
        result_text = '';

        rally_array.push($(this).val());
        //console.log(rally_array.length);
        if (service_flag==true) { //サービス権あり
            if (court_flag==true) { //コート上
                result_text += '(サ)'+target_name + ': ';
            }else{
                result_text += '(サ)'+target_name + ': ';
            }
        }else{ //サービス権なし
            if (court_flag==true) {
                result_text += '(サ)'+opponent_name + ': ';
            }else{
                result_text += '(サ)'+opponent_name + ': ';
            }
        }
        result_text += JSON.stringify(rally_array);

        $('#result').html(result_text);

        if (service_flag==true) {
            if (rally_array.length>3) {
                $('#submit_bt').prop("disabled", false);
            }
        }else{
            if (rally_array.length>4) {
                $('#submit_bt').prop("disabled", false);
            }
        }
    });

    //データの削除ボタンを押したときに発火
    $('body').on('click', '.remove_history', function(event){
        //console.log($(this).val() + ': 削除ボタンが押されました');
        $("#result").html("削除しました");
        $.ajax({
            type: "POST",
            url: "./remove_game_data.php",
            data: { 'id': $(this).val(),
                    'token': token
                },
            dataType: "json"
        }).done(function(data){
            table_text = '';
            for (key in data) {
                table_text += '<tr>';
                table_text += '<td>' + data[key] + '</td>';
                table_text += '<td><button class="btn btn-danger btn-sm remove_history" value="' + key + '">削除</button></td>'
                table_text += '</tr>';

            }
            $('#history_table').html(table_text);
        }).fail(function(XMLHttpRequest, status, e){
            alert(e);
        });
    });

    //サービス位置の変更
    function showServicePosition() {

        //分析対象選手がサービスの場合
        if (service_flag==true) {
            if (court_flag==true) { //コート上
                $('#service_in_upper').html('サービス('+target_name+')');
                $('#service_in_bottom').html('レシーブ('+opponent_name+')');
                if (rally_array.length==0) { //サービスがまだ
                    $('#court_u1').prop("disabled", false);
                    $('#court_u2').prop("disabled", false);
                    $('#court_b1').prop("disabled", true);
                    $('#court_b2').prop("disabled", true);
                }
            }else{ //コート下
                $('#service_in_upper').html('レシーブ('+opponent_name+')');
                $('#service_in_bottom').html('サービス('+target_name+')');
                if (rally_array.length==0) { //サービスがまだ
                    $('#court_u1').prop("disabled", true);
                    $('#court_u2').prop("disabled", true);
                    $('#court_b1').prop("disabled", false);
                    $('#court_b2').prop("disabled", false);
                }
            }

        }else{ //対戦相手がサービスの場合
            if (court_flag==true) { //コート上
                $('#service_in_upper').html('レシーブ('+target_name+')');
                $('#service_in_bottom').html('サービス('+opponent_name+')');
                if (rally_array.length==0) { //サービスがまだ
                    $('#court_u1').prop("disabled", true);
                    $('#court_u2').prop("disabled", true);
                    $('#court_b1').prop("disabled", false);
                    $('#court_b2').prop("disabled", false);
                }
            }else{ //コート下
                $('#service_in_upper').html('サービス('+opponent_name+')');
                $('#service_in_bottom').html('レシーブ('+target_name+')');
                if (rally_array.length==0) { //サービスがまだ
                    $('#court_u1').prop("disabled", false);
                    $('#court_u2').prop("disabled", false);
                    $('#court_b1').prop("disabled", true);
                    $('#court_b2').prop("disabled", true);
                }
            }
        }
    }

    //リセットボタンが押された
    $("#reset_bt").on("click", function(event){
        //$("#result").html("リセットボタンが押された");
        resetFuntion();
    });

    //登録ボタンが押された
    $("#submit_bt").on("click", function(event){
        /*
        console.log('選手1: '+ target_name);
        console.log('試合名: '+ game_title);
        console.log('選手2: ' + opponent_name);
        console.log('選手2所属: '+ opponent_affiliation);
        let dominant = $('input[name="dominant"]:checked').val();
        if (dominant==0) {
            console.log('右');
        }else{
            console.log('左');
        }
        console.log(rally_array);
        console.log('token: '+token);
        */
        let dominant = $('input[name="dominant"]:checked').val();
        $.ajax({
            type: "POST",
            url: "./game_registration.php",
            data: { 'player_id1': player_id1,
                    'player1': target_name,
                    'game_title': game_title,
                    'player2': opponent_name,
                    'player2_affiliation':opponent_affiliation,
                    'service_flag': service_flag,
                    'dominant': dominant,
                    'rally_array': JSON.stringify(rally_array),
                    'token': token,
                    'user_id': user_id
                  },
            dataType: "json"
        }).done(function(data){
            table_text = '';
            for (key in data) {
                table_text += '<tr>';
                table_text += '<td><button class="btn btn-danger btn-sm remove_history" value="' + key + '">削除</button></td>';
                table_text += '<td>' + data[key] + '</td>';
                table_text += '</tr>';

            }
            $('#history_table').html(table_text);
            console.log(data);
        }).fail(function(XMLHttpRequest, status, e){
            alert(e);
        });

        resetFuntion();
        $("#result").html("登録しました");
    });

    //リセット
    function resetFuntion() {
        rally_array = []; //ラリーのリセット
        //サービス交代とコート交代を使用可能にする
        $('#serviceChange').prop("disabled", false);
        $('#courtChange').prop("disabled", false);
        showServicePosition();
        //リセットと登録ボタンを使用不可にする
        $('#submit_bt').prop("disabled", true);
        $('#reset_bt').prop("disabled", true);
    }
});