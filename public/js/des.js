let deResult=Array("rotate3d(1, 1, 0,180deg)","rotate3d(1, 0, 1,180deg)","rotate3d(0, 1, 1,180deg)","rotate3d(0, -1, 1,180deg)","rotate3d(1, 0, -1,180deg)","rotate3d(0, 1, 1,1turn)");
let deClicked=false;
let AnimRandom1;
let AnimRandom2;
let AnimRandom3;
let score

/*function deAnim(){
    if(!deClicked) {
        $('.dé_cube').css({'transform': 'rotate3d(1, 1, 0,180deg)'});
        setTimeout(deAnim2, 200);
    }
    else{
        random();
    }
}
function deAnim2(){
    if(!deClicked){
        $('.dé_cube').css('transform','rotate3d(0, 1, 1,180deg)');
        setTimeout(deAnim3,200);
    }
    else{
        random();
    }
}
function deAnim3(){
    if(!deClicked){
        $('.dé_cube').css('transform','rotate3d(1, 0, 1,270deg)');
    }
    else{
        random();
    }
}*/
function deAnim(){
    AnimRandom1=Math.round(Math.random());
    AnimRandom2=Math.round(Math.random());
    AnimRandom3=Math.round(Math.random());
    if(!deClicked) {
        $('.dé_wrap:nth-child(1) .dé_cube').css({'transform': 'rotate3d('+AnimRandom1+', '+AnimRandom2+', '+AnimRandom3+',180deg)'});
        $('.dé_wrap:nth-child(2) .dé_cube').css({'transform': 'rotate3d('+1-AnimRandom1+', '+1-AnimRandom2+', '+1-AnimRandom3+',180deg)'});
        setTimeout(deAnim2,100);
    }
    else{
        random();
    }
}
function deAnim2(){
    if(!deClicked){
        $('.dé_wrap:nth-child(1) .dé_cube').css({'transform': 'rotate3d('+1-AnimRandom1+', '+1-AnimRandom2+', '+1-AnimRandom3+',180deg)'});
        $('.dé_wrap:nth-child(2) .dé_cube').css({'transform': 'rotate3d('+AnimRandom1+', '+AnimRandom2+', '+AnimRandom3+',180deg)'});
    }
    else{
        random();
    }
}

deAnim();
let deInterval=setInterval(deAnim,200);
$('.dé_wrap').click(function(){
    clickedTest();
});
function random(){
    let rand1=Math.floor(Math.random()*6+1);
    let rand2=Math.floor(Math.random()*6+1);
    score=rand1+rand2;
    $('#dé_cube1').css({'transform':deResult[rand1-1]});
    $('#dé_cube2').css({'transform':deResult[rand2-1]});
    $('#score').text('BAM ! Ça fait '+score);
    if((rand1+rand2)==12){
        $('#score').text($('#score').text()+' ! GG ma couille');
    }
}
function clickedTest(){
    if(!deClicked){
        deClicked=true;
        setTimeout(function(){clearInterval(deInterval)},200);
    }
    else{
        deout();
        //random();
    }
}
function deout(){
    $('#wrapper_des').css('top','100vh');
    shdw_out();
    setTimeout(playerFocus,500);
}
$(document).on('keydown',function(e){
    if(e.keyCode==32){//next
        clickedTest();
    }
});