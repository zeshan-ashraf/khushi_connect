<!-- Server Upgrade Notification -->
<div class="server-upgrade-notification">
    <div class="container">
        <div class="notification-banner">
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="fas fa-server"></i>
                </div>
                <div class="notification-text">
                    <h4>🚀 Enhanced Server Performance</h4>
                    <p>We've upgraded to a powerful VPS server for faster, more reliable service. Experience improved speed and enhanced security for all your transactions.</p>
                </div>
                <div class="notification-close">
                    <button type="button" class="close-btn" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.server-upgrade-notification {
    position: relative;
    z-index: 1000;
    margin-bottom: 0;
}

.notification-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-bottom: 3px solid #5a67d8;
    animation: slideDown 0.5s ease-out;
}

.notification-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    gap: 20px;
}

.notification-icon {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    backdrop-filter: blur(10px);
}

.notification-text {
    flex: 1;
}

.notification-text h4 {
    margin: 0 0 5px 0;
    font-size: 18px;
    font-weight: 600;
    color: white;
}

.notification-text p {
    margin: 0;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.4;
}

.notification-close {
    flex-shrink: 0;
}

.close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .notification-content {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .notification-text h4 {
        font-size: 16px;
    }
    
    .notification-text p {
        font-size: 13px;
    }
    
    .notification-close {
        position: absolute;
        top: 10px;
        right: 15px;
    }
    
    .close-btn {
        width: 30px;
        height: 30px;
    }
}

@media (max-width: 480px) {
    .notification-banner {
        padding: 12px 0;
    }
    
    .notification-content {
        padding: 0 15px;
    }
    
    .notification-text h4 {
        font-size: 15px;
    }
    
    .notification-text p {
        font-size: 12px;
    }
}
</style>
