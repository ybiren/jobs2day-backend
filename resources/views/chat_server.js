require('dotenv').config();
const express = require('express');
const WebSocket = require('ws');
const redis = require('redis');

const app = express();
const server = require('http').createServer(app);
const wss = new WebSocket.Server({ server });

// Redis connection with authentication
const redisConfig = {
    socket: {
        host: process.env.REDIS_HOST || '127.0.0.1',
        port: process.env.REDIS_PORT || 6379
    },
    password: process.env.REDIS_PASSWORD || 'furqan'
};

// Create Redis clients
const redisPublisher = redis.createClient(redisConfig);
const redisSubscriber = redis.createClient(redisConfig);

(async () => {
    try {
        await redisPublisher.connect();
        await redisSubscriber.connect();
        console.log("✅ Connected to Redis successfully");
    } catch (error) {
        console.error("❌ Redis Connection Error:", error);
    }
})();

// Handle Redis errors
redisPublisher.on('error', (err) => console.error('🚨 Redis Publisher Error:', err));
redisSubscriber.on('error', (err) => console.error('🚨 Redis Subscriber Error:', err));

app.get('/', (req, res) => {
    res.send("✅ WebSocket server is running!");
});

const CHANNEL = 'chat_messages';

// Function to handle incoming messages
const handleMessage = (message) => {
    try {
        console.log('📢 Broadcasting message:', message);
        wss.clients.forEach((client) => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(message);
            }
        });
    } catch (error) {
        console.error('⚠️ Error broadcasting message:', error);
    }
};

// Subscribe to Redis channel
redisSubscriber.subscribe(CHANNEL, handleMessage);

wss.on('connection', (ws) => {
    console.log('🔌 New client connected');

    ws.on('message', async (message) => {
        try {
            const msg = message.toString(); // Convert Buffer to String
            const parsedMessage = JSON.parse(msg); // Parse JSON

            const senderId = parsedMessage.sender_id || "Unknown";
            const receiverId = parsedMessage.receiver_id || "Broadcast";
            const textMessage = parsedMessage.message || "";
            const timestamp = new Date().toISOString(); // Get timestamp

            console.log(`📥 Received Message:\n🆔 Sender: ${senderId}\n🎯 Receiver: ${receiverId}\n💬 Message: ${textMessage}\n⏰ Date: ${timestamp}`);

            // Publish structured JSON to Redis
            await redisPublisher.publish(CHANNEL, JSON.stringify({
                sender_id: senderId,
                receiver_id: receiverId,
                message: textMessage,
                date: timestamp
            }));
        } catch (err) {
            console.error('❌ Message Processing Error:', err);
        }
    });


    ws.on('close', () => {
        console.log('🚪 Client disconnected');
    });
});


// **Keep server alive (Ping mechanism)**
setInterval(() => {
    console.log("🔄 Pinging the server to keep it alive...");
    require('http').get(`http://localhost:${process.env.PORT || 3001}/`, (res) => {
        res.on('data', () => {}); // Consume response
    }).on('error', (err) => console.error('⚠️ Ping failed:', err));
}, 5 * 60 * 1000);

// Start the server on 0.0.0.0 for external access
const PORT = process.env.PORT || 3001;
server.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 WebSocket server running on port ${PORT}`);
});

// **WebSocket Keep-Alive Ping**
setInterval(() => {
    console.log("📡 Sending keep-alive ping...");
    wss.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify({ type: "ping" }));
        }
    });
}, 30000);



// for redis
//
//     /etc/supervisor/conf.d
//         [program:laravel-worker]
// process_name=%(program_name)s_%(process_num)02d
// command=php /var/www/html/jobs2day/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
// autostart=true
// autorestart=true
// numprocs=3
// user=root
// redirect_stderr=true
// stdout_logfile=/var/log/laravel-worker.log
// stopwaitsecs=3600



