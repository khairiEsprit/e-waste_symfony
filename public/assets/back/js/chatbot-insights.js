/**
 * Chatbot Insights Module
 * Adds real-time waste collection insights functionality to the admin dashboard chatbot
 */

class ChatbotInsights {
    constructor(chatbot) {
        this.chatbot = chatbot;
        this.messagesContainer = document.getElementById('chatMessages');
        this.insightButtons = null;
        this.wasteData = null;
        
        // Initialize the module
        this.init();
    }
    
    /**
     * Initialize the module
     */
    init() {
        // Fetch waste collection data
        this.fetchWasteData();
        
        // Add insight buttons when chat is opened
        document.getElementById('chatButton').addEventListener('click', () => {
            // Add a slight delay to ensure the chat window is open
            setTimeout(() => {
                this.addInsightButtons();
            }, 300);
        });
    }
    
    /**
     * Fetch waste collection data (using static data for now)
     */
    fetchWasteData() {
        // Static data based on MapActivityController
        this.wasteData = {
            bins: [
                {
                    id: 1,
                    name: 'Trash Container #1',
                    longitude: 10.181667,
                    latitude: 36.806389,
                    fillLevel: 85,
                    status: 'Critical',
                    lastCollected: '2023-06-15 08:30:00',
                    isCollected: false
                },
                {
                    id: 2,
                    name: 'Trash Container #2',
                    longitude: 10.184667,
                    latitude: 36.809389,
                    fillLevel: 70,
                    status: 'Warning',
                    lastCollected: '2023-06-15 09:15:00',
                    isCollected: false
                },
                {
                    id: 3,
                    name: 'Trash Container #3',
                    longitude: 10.179667,
                    latitude: 36.803389,
                    fillLevel: 45,
                    status: 'Normal',
                    lastCollected: '2023-06-15 10:00:00',
                    isCollected: true
                },
                {
                    id: 4,
                    name: 'Trash Container #4',
                    longitude: 10.186667,
                    latitude: 36.801389,
                    fillLevel: 30,
                    status: 'Normal',
                    lastCollected: '2023-06-15 10:45:00',
                    isCollected: true
                },
                {
                    id: 5,
                    name: 'Trash Container #5',
                    longitude: 10.182667,
                    latitude: 36.807389,
                    fillLevel: 60,
                    status: 'Warning',
                    lastCollected: '2023-06-15 11:30:00',
                    isCollected: false
                }
            ],
            trucks: [
                {
                    id: 1,
                    name: 'Truck #1',
                    status: 'On Route',
                    currentLocation: 'Near Trash Container #2',
                    completedBins: 2,
                    totalBins: 5
                },
                {
                    id: 2,
                    name: 'Truck #2',
                    status: 'At Depot',
                    currentLocation: 'Collection Center',
                    completedBins: 0,
                    totalBins: 0
                }
            ],
            sensors: [
                {
                    id: 1,
                    name: 'Sensor #1',
                    binId: 1,
                    status: 'Normal',
                    lastReading: '2023-06-15 12:00:00',
                    batteryLevel: '85%'
                },
                {
                    id: 2,
                    name: 'Sensor #2',
                    binId: 2,
                    status: 'Normal',
                    lastReading: '2023-06-15 12:05:00',
                    batteryLevel: '90%'
                },
                {
                    id: 3,
                    name: 'Sensor #3',
                    binId: 3,
                    status: 'Anomaly Detected',
                    lastReading: '2023-06-15 12:10:00',
                    batteryLevel: '75%'
                },
                {
                    id: 4,
                    name: 'Sensor #4',
                    binId: 4,
                    status: 'Normal',
                    lastReading: '2023-06-15 12:15:00',
                    batteryLevel: '95%'
                },
                {
                    id: 5,
                    name: 'Sensor #5',
                    binId: 5,
                    status: 'Low Battery',
                    lastReading: '2023-06-15 12:20:00',
                    batteryLevel: '15%'
                }
            ]
        };
    }
    
    /**
     * Add insight buttons to the chatbot
     */
    addInsightButtons() {
        // Check if buttons already exist
        if (this.insightButtons) return;
        
        // Create buttons container
        this.insightButtons = document.createElement('div');
        this.insightButtons.className = 'insight-buttons';
        this.insightButtons.style.display = 'flex';
        this.insightButtons.style.flexWrap = 'wrap';
        this.insightButtons.style.gap = '8px';
        this.insightButtons.style.marginTop = '10px';
        this.insightButtons.style.marginBottom = '15px';
        
        // Add buttons for different insights
        const buttons = [
            { text: 'Collection Status', query: 'show me today\'s waste collection status' },
            { text: 'Bin Conditions', query: 'show me overflowing bins' },
            { text: 'Truck Routes', query: 'show me route progress of collection trucks' },
            { text: 'Sensor Anomalies', query: 'show me sensor anomalies' }
        ];
        
        buttons.forEach(button => {
            const btn = document.createElement('button');
            btn.textContent = button.text;
            btn.className = 'insight-button';
            btn.style.backgroundColor = '#4F46E5';
            btn.style.color = 'white';
            btn.style.border = 'none';
            btn.style.borderRadius = '4px';
            btn.style.padding = '8px 12px';
            btn.style.fontSize = '12px';
            btn.style.cursor = 'pointer';
            btn.style.transition = 'background-color 0.2s';
            
            btn.addEventListener('mouseenter', () => {
                btn.style.backgroundColor = '#4338CA';
            });
            
            btn.addEventListener('mouseleave', () => {
                btn.style.backgroundColor = '#4F46E5';
            });
            
            btn.addEventListener('click', () => {
                this.handleInsightQuery(button.query);
            });
            
            this.insightButtons.appendChild(btn);
        });
        
        // Add buttons after the first bot message
        const firstBotMessage = this.messagesContainer.querySelector('.message.bot');
        if (firstBotMessage) {
            firstBotMessage.appendChild(this.insightButtons);
        }
    }
    
    /**
     * Handle insight query
     * @param {string} query - The insight query
     */
    handleInsightQuery(query) {
        // Add user message
        this.chatbot.addMessage(query, 'user');
        
        // Generate response based on query
        let response = '';
        
        if (query.includes('collection status')) {
            response = this.generateCollectionStatusResponse();
        } else if (query.includes('overflowing bins')) {
            response = this.generateBinConditionsResponse();
        } else if (query.includes('truck') || query.includes('route')) {
            response = this.generateTruckRoutesResponse();
        } else if (query.includes('sensor') || query.includes('anomalies')) {
            response = this.generateSensorAnomaliesResponse();
        } else {
            response = 'I\'m not sure how to help with that. Please try one of the quick access buttons for waste collection insights.';
        }
        
        // Add bot response
        this.chatbot.addMessage(response, 'bot');
    }
    
    /**
     * Generate collection status response
     * @returns {string} - The formatted response
     */
    generateCollectionStatusResponse() {
        const collectedBins = this.wasteData.bins.filter(bin => bin.isCollected).length;
        const totalBins = this.wasteData.bins.length;
        const uncollectedBins = totalBins - collectedBins;
        const criticalBins = this.wasteData.bins.filter(bin => bin.status === 'Critical').length;
        
        return `📊 **Today's Waste Collection Status**\n\n` +
               `- Bins collected: ${collectedBins}/${totalBins} (${Math.round(collectedBins/totalBins*100)}%)\n` +
               `- Bins awaiting collection: ${uncollectedBins}\n` +
               `- Overflowing bins: ${criticalBins}\n` +
               `- Active collection trucks: ${this.wasteData.trucks.filter(truck => truck.status === 'On Route').length}\n\n` +
               `Would you like more detailed information about specific bins or trucks?`;
    }
    
    /**
     * Generate bin conditions response
     * @returns {string} - The formatted response
     */
    generateBinConditionsResponse() {
        const criticalBins = this.wasteData.bins.filter(bin => bin.status === 'Critical');
        const warningBins = this.wasteData.bins.filter(bin => bin.status === 'Warning');
        
        let response = `🗑️ **Bin Conditions Report**\n\n`;
        
        if (criticalBins.length > 0) {
            response += `**Critical Bins (${criticalBins.length}):**\n`;
            criticalBins.forEach(bin => {
                response += `- ${bin.name}: ${bin.fillLevel}% full, ${bin.isCollected ? 'collected' : 'not collected'}\n`;
            });
            response += '\n';
        } else {
            response += `**Critical Bins:** None\n\n`;
        }
        
        if (warningBins.length > 0) {
            response += `**Warning Bins (${warningBins.length}):**\n`;
            warningBins.forEach(bin => {
                response += `- ${bin.name}: ${bin.fillLevel}% full, ${bin.isCollected ? 'collected' : 'not collected'}\n`;
            });
        } else {
            response += `**Warning Bins:** None\n`;
        }
        
        return response;
    }
    
    /**
     * Generate truck routes response
     * @returns {string} - The formatted response
     */
    generateTruckRoutesResponse() {
        let response = `🚚 **Collection Truck Routes**\n\n`;
        
        this.wasteData.trucks.forEach(truck => {
            const progressPercent = truck.completedBins > 0 ? Math.round(truck.completedBins/truck.totalBins*100) : 0;
            response += `**${truck.name}**\n` +
                       `- Status: ${truck.status}\n` +
                       `- Location: ${truck.currentLocation}\n` +
                       `- Progress: ${truck.completedBins}/${truck.totalBins} bins (${progressPercent}%)\n\n`;
        });
        
        return response;
    }
    
    /**
     * Generate sensor anomalies response
     * @returns {string} - The formatted response
     */
    generateSensorAnomaliesResponse() {
        const anomalySensors = this.wasteData.sensors.filter(sensor => 
            sensor.status !== 'Normal');
        
        let response = `⚠️ **Sensor Anomalies Report**\n\n`;
        
        if (anomalySensors.length > 0) {
            response += `Found ${anomalySensors.length} sensors with anomalies:\n\n`;
            
            anomalySensors.forEach(sensor => {
                const bin = this.wasteData.bins.find(bin => bin.id === sensor.binId);
                response += `**${sensor.name} (${bin ? bin.name : 'Unknown bin'})**\n` +
                           `- Status: ${sensor.status}\n` +
                           `- Last reading: ${sensor.lastReading}\n` +
                           `- Battery level: ${sensor.batteryLevel}\n\n`;
            });
        } else {
            response += `All sensors are functioning normally. No anomalies detected.`;
        }
        
        return response;
    }
}

// Initialize the insights module when the page loads
document.addEventListener('DOMContentLoaded', () => {
    // Wait for the Chatbot class to be initialized
    const checkChatbot = setInterval(() => {
        if (window.chatbot) {
            new ChatbotInsights(window.chatbot);
            clearInterval(checkChatbot);
        }
    }, 100);
});