
/**
 * @param divID  注册的divID
 * @param ImagePath 视频首次的图片
 * @param VideoFile 视频文件
 * @param Description 描述
 * @param Title 标题
 * @param C_srt 中文字幕路径
 * @param E_srt 英文字幕路径
 * @param WidthPx 宽
 * @param HeightPx 高
 * @constructor 播放器的调用方法
 */
function setJWPlayer(divID,ImagePath,VideoFile,Title,Description,C_srt,E_srt,WidthPx,HeightPx){
    Title ="";// 默认为空
    jwplayer(divID).setup({
        playlist: [{

            image: ImagePath,

            sources: [
                {file: VideoFile,label:"640p flv"}
            ],

            title: Title,

            description: Description,
            skin: "five",
            tracks: [{

                file: C_srt,

                label: "中文",

                kind: "captions",
                "default": true

            },{
                file: E_srt,

                label: "English",

                kind: "captions"
            }]}

        ],

        width: WidthPx,

        height: HeightPx

    });


}

/**
 * 弹出分值增加的函数
 * @type {*|Object}
 */
var x = window.x || {};
x.creat = function (t, b, c, d, sourceHtml) {
    this.t = t;
    this.b = b;
    this.c = c;
    this.d = d;
    this.op = 1;
    this.div = document.createElement("div");
    this.div.style.height = "40px";
    this.div.style.width = "80px";
    this.div.style.background = "red";
    this.div.style.position = "absolute";
    this.div.style.left = "60%";
    this.div.style.marginLeft = "-50px";
    this.div.style.marginTop = "-20px";
    this.div.innerHTML = sourceHtml;
    this.div.style.fontSize = "12";
    this.div.style.lineHeight = this.div.style.height;
    this.div.style.textAlign = "center";
    this.div.style.fontWeight = "bold";
    this.div.style.color = "#fff";
    this.div.style.top = (this.b + "%");
    document.body.appendChild(this.div);
    this.run();
}
x.creat.prototype = {
    run:function () {
        var me = this;
        this.div.style.top = -this.c * (this.t / this.d) * (this.t / this.d) + this.b + "%";
        this.t++;
        this.q = setTimeout(function () {
            me.run();
        }, 25);
        if (this.t == this.d) {
            clearTimeout(me.q);
            setTimeout(function () {
                me.alpha();
            }, 1000);
        }
    },
    alpha:function () {
        var me = this;
        if ("\v" == "v") {
            this.div.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.op * 100 + ")";
            this.div.style.filter = "alpha(opacity=" + this.op * 100 + ")";

        }
        else {
            this.div.style.opacity = this.op
        }
        this.op -= 0.02;
        this.w = setTimeout(function () {
            me.alpha();
        }, 25);
        if (this.op <= 0) {
            clearTimeout(this.w);
            document.body.removeChild(me.div);
        }
    }
}
//  用法 +5 为飘过的分值
//new x.creat(1, 50, 25, 30, "+5");

