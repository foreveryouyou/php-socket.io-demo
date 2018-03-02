(function () {
    var WS = function (arg) {
        if (typeof arg !== 'object') {
            throw '构造方法需要一个对象作为参数';
        }
        if (!arg.io) {
            throw '参数中缺少socket.io对象';
        }
        this.io = io;
        this.init(arg);
    };
    Object.assign(WS.prototype, {
        io: null, // socket.io类
        socket: null, // socket.io实例
        userId: '', // 用户id
        sessionId: '', // 会话id, 不一定真的是session
        cbConnected: function () {
            // 连接建立回调
        },
        cbClose: function () {
            // 连接断开回调
        },
        cbError: function () {
            // 连接出错回调
        },
        cbInfo: function () {
            // 信息更新回调
        },
        init: function (arg) {
            if (typeof arg !== 'object') {
                throw 'init(arg)需要一个对象作为参数';
            }
            if (!arg.url) {
                throw '请指定服务器地址';
            }
            this.url = arg.url;
            if (arg.cbConnected && typeof arg.cbConnected === 'function') {
                this.cbConnected = arg.cbConnected;
            }
            if (arg.cbClose && typeof arg.cbClose === 'function') {
                this.cbClose = arg.cbClose;
            }
            if (arg.cbError && typeof arg.cbError === 'function') {
                this.cbError = arg.cbError;
            }
            if (arg.cbInfo && typeof arg.cbInfo === 'function') {
                this.cbInfo = arg.cbInfo;
            }
            this.connect();
        },
        connect: function () {
            var socket = this.socket = this.io(this.url);
            var that = this;
            // 当连接服务端成功时触发connect默认事件
            socket.on('connect', function (msg) {
                var arg = {
                    socketId: socket.id
                };
                that.cbConnected(arg);
                socket.emit('admin connect');
            });
            socket.on('admin info', function (data) {
                that.cbInfo(data);
            });
        },
        disconnect: function () {
            if (this.socket) {
                this.socket.close();
                this.cbClose();
            }
        }
    });
    window.WS = WS;
}());