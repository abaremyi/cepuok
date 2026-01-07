// js/dynamic-content.js
class DynamicContentManager {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        this.cache = {};
    }

    async loadPageContent(pageName) {
        try {
            const response = await fetch(`${this.baseUrl}/api/page-content?page=${pageName}`);
            const data = await response.json();
            
            if (data.success) {
                this.cache[pageName] = data.data;
                return data.data;
            }
            return null;
        } catch (error) {
            console.error('Error loading page content:', error);
            return null;
        }
    }

    async loadEducationalPrograms() {
        try {
            const response = await fetch(`${this.baseUrl}/api/educational-programs`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error loading educational programs:', error);
            return [];
        }
    }

    async loadWhyChooseItems() {
        try {
            const response = await fetch(`${this.baseUrl}/api/why-choose`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error loading why choose items:', error);
            return [];
        }
    }

    async loadQuickStats() {
        try {
            const response = await fetch(`${this.baseUrl}/api/quick-stats`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error loading quick stats:', error);
            return [];
        }
    }

    getContent(sectionName) {
        // Try to get from cache first
        for (const page in this.cache) {
            if (this.cache[page][sectionName]) {
                return this.cache[page][sectionName];
            }
        }
        return null;
    }

    updateElement(elementId, content) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = content;
        }
    }

    updateImage(elementId, imageUrl, altText = '') {
        const element = document.getElementById(elementId);
        if (element) {
            element.src = imageUrl;
            if (altText) element.alt = altText;
        }
    }
}

// Create global instance
const dynamicContent = new DynamicContentManager(BASE_URL);

// Initialize on page load
document.addEventListener('DOMContentLoaded', async function() {
    // Load homepage content
    await dynamicContent.loadPageContent('home');
    
    // Update specific elements if needed
    // Example: updateElement('welcome-title', dynamicContent.getContent('welcome_section_title')?.content);
});