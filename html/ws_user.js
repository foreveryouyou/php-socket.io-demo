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
        debug: false,
        io: null, // socket.io类
        socket: null, // socket.io实例
        userId: '', // 用户id
        sessionId: '', // 会话id, 不一定真的是session
        lsKey: 'wsUserInfo', // localStorage中保存userInfo的键
        cbConnected: function () {
            // 连接建立回调
        },
        cbClose: function () {
            // 连接断开回调
        },

        init: function (arg) {
            if (typeof arg !== 'object') {
                throw 'init(arg)需要一个对象作为参数';
            }
            if (!arg.url) {
                throw '请指定服务器地址';
            }
            for (var prop in arg) {
                if (arg.hasOwnProperty(prop) && this.hasOwnProperty(prop)) {
                    this[prop] = arg[prop];
                }
            }
            this.url = arg.url;
            if (arg.cbConnected && typeof arg.cbConnected === 'function') {
                this.cbConnected = arg.cbConnected;
            }
            if (arg.cbClose && typeof arg.cbClose === 'function') {
                this.cbClose = arg.cbClose;
            }
            this.checkUserInfo();
            var that = this;
            window.addEventListener('storage', function (e) {
                if (e.key === that.lsKey) {
                    that.checkUserInfo();
                }
            });
        },
        checkUserInfo: function () {
            var userInfo = false;
            try {
                userInfo = JSON.parse(localStorage.getItem(this.lsKey));
            } catch (e) {
                that.log(e.toString());
                return e;
            }
            this.userId = userInfo.userId || '';
            this.sessionId = userInfo.sessionId || '';
            if (this.userId && this.sessionId) {
                this.connect();
            } else {
                this.disconnect();
            }
        },
        connect: function () {
            this.socket = this.socket || this.io(this.url);
            var that = this;
            // 当连接服务端成功时触发connect默认事件
            this.socket.on('connect', function () {
                that.log('connect success, connId:', that.socket.id);
                that.socket.emit('user connect', that.userId, that.sessionId);
            });
            this.socket.on('user connected', function (msg) {
                that.log('user connected: ' + msg + ' from server');
            });
        },
        disconnect: function () {
            if (this.socket) {
                this.socket.close();
            }
        },
        login: function () {

        },
        logout: function () {

        },
        log: function () {
            this.debug && console.log.apply(this, arguments);
        }
    });
    window.WS = WS;
}());