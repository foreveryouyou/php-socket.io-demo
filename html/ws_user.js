(function () {
    var WS = function (io, url) {
        if (!io) {
            throw '缺少socket.io';
        }
        this.io = io;
        this.init(url);
    };
    Object.assign(WS.prototype, {
        io: null, // socket.io类
        socket: null, // socket.io实例
        userId: '', // 用户id
        sessionId: '', // 会话id, 不一定真的是session
        lsKey: 'wsUserInfo', // localStorage中保存userInfo的键
        init: function (url) {
            if (!url) {
                throw '请指定服务器地址';
            }
            this.url = url;
            this.checkUserInfo();

            var that = this;
            window.addEventListener('storage', function (e) {
                if (e.key === that.lsKey) {
                    that.checkUserInfo();
                }
            });
        },
        checkUserInfo: function () {
            var userInfo = {};
            try {
                userInfo = JSON.parse(localStorage.getItem(this.lsKey)) || {};
            } catch (e) {
                console.log(e.toString());
                return e;
            }
            console.log(userInfo);
            this.userId = userInfo.userId || '';
            this.sessionId = userInfo.sessionId || '';
            if (this.userId && this.sessionId) {
                this.connect();
            }
        },
        connect: function () {
            var socket = this.socket = this.io(this.url);
            var that = this;
            // 当连接服务端成功时触发connect默认事件
            socket.on('connect', function (msg) {
                console.log('connect success, connId:', socket.id);
                socket.emit('user connect', that.userId, that.sessionId);
            });
            // 服务端通过emit('chat message from server', $msg)触发客户端的chat message from server事件
            socket.on('group info', function (msg) {
                console.log('get message:' + msg + ' from server');
            });
            socket.on('user connected', function (msg) {
                console.log('user connected: ' + msg + ' from server');
            });
        },
        disconnect: function () {
            if (this.socket) {
                this.socket.close();
            }
        }
    });
    window.WS = WS;
}());