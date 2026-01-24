/**
 * Sistema de navegación con Gamepad
 * Permite usar un control para navegar por la interfaz
 */
class GamepadNavigation {
    constructor() {
        this.selectedElement = null;
        this.focusableElements = [];
        this.gamepadConnected = false;
        this.debounceTimer = null;
        this.debounceDelay = 150; // ms de espera entre inputs
        
        this.initGamepad();
        this.updateFocusableElements();
    }
    
    initGamepad() {
        try {
            const handler = new GamepadHandler();
            
            // DEBUG: Mostrar todos los botones presionados
            handler.on('buttondown', (e) => {
                console.log('Botón presionado:', e.label, '(índice:', e.index, ')');
            });
            
            // Detectar botones del D-Pad
            handler.on('buttondown', (e) => {
                if (e.label === 'DPAD_UP') {
                    console.log('→ Ejecutando navigateUp()');
                    this.navigateUp();
                } else if (e.label === 'DPAD_DOWN') {
                    console.log('→ Ejecutando navigateDown()');
                    this.navigateDown();
                } else if (e.label === 'DPAD_LEFT') {
                    console.log('→ Ejecutando navigateLeft()');
                    this.navigateLeft();
                } else if (e.label === 'DPAD_RIGHT') {
                    console.log('→ Ejecutando navigateRight()');
                    this.navigateRight();
                } else if (e.label === 'BUTTON_1' || e.label === 'START') {
                    // Botón A o Start para confirmar
                    if (this.selectedElement) {
                        this.selectedElement.click();
                    }
                }
            });
            
            // Detectar sticks analógicos
            handler.on('axischanged', (e) => {
                // Ignoramos los sticks analógicos para evitar conflictos
                // Solo usamos la cruceta digital (D-Pad)
                console.log('Eje detectado:', e.axis, '(ignorado)');
            });
            
            handler.on('connected', (e) => {
                console.log('Gamepad conectado:', e.gamepadIndex);
                this.gamepadConnected = true;
                this.focusFirst();
            });
            
            handler.on('disconnected', (e) => {
                console.log('Gamepad desconectado:', e.gamepadIndex);
                this.gamepadConnected = false;
            });
            
        } catch(err) {
            console.log('GamepadHandler no disponible, navegación con gamepad deshabilitada');
        }
    }
    
    updateFocusableElements() {
        // Obtener todos los elementos navegables
        this.focusableElements = Array.from(document.querySelectorAll(
            'a, button, input[type="text"], input[type="password"]'
        )).filter(el => {
            return el.offsetParent !== null; // Solo elementos visibles
        });
    }
    
    debouncedNavigate(direction) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            if (direction === 'up') this.navigateUp();
            else if (direction === 'down') this.navigateDown();
            else if (direction === 'left') this.navigateLeft();
            else if (direction === 'right') this.navigateRight();
        }, this.debounceDelay);
    }
    
    navigateUp() {
        // Para grillas: ir arriba
        this.updateFocusableElements();
        if (!this.selectedElement) {
            this.focusFirst();
            return;
        }
        
        const rect = this.selectedElement.getBoundingClientRect();
        const candidates = this.focusableElements.filter(el => {
            const elRect = el.getBoundingClientRect();
            return Math.abs(elRect.left - rect.left) < 50 && elRect.top < rect.top;
        });
        
        if (candidates.length > 0) {
            const closest = candidates.reduce((prev, curr) => {
                return (curr.getBoundingClientRect().top > prev.getBoundingClientRect().top) ? curr : prev;
            });
            this.focusElement(closest);
        }
    }
    
    navigateDown() {
        // Para grillas: ir abajo
        this.updateFocusableElements();
        if (!this.selectedElement) {
            this.focusFirst();
            return;
        }
        
        const rect = this.selectedElement.getBoundingClientRect();
        const candidates = this.focusableElements.filter(el => {
            const elRect = el.getBoundingClientRect();
            return Math.abs(elRect.left - rect.left) < 50 && elRect.top > rect.top;
        });
        
        if (candidates.length > 0) {
            const closest = candidates.reduce((prev, curr) => {
                return (curr.getBoundingClientRect().top < prev.getBoundingClientRect().top) ? curr : prev;
            });
            this.focusElement(closest);
        }
    }
    
    navigateLeft() {
        // Para grillas: ir izquierda
        this.updateFocusableElements();
        if (!this.selectedElement) {
            this.focusFirst();
            return;
        }
        
        const rect = this.selectedElement.getBoundingClientRect();
        const candidates = this.focusableElements.filter(el => {
            const elRect = el.getBoundingClientRect();
            return Math.abs(elRect.top - rect.top) < 50 && elRect.left < rect.left;
        });
        
        if (candidates.length > 0) {
            const closest = candidates.reduce((prev, curr) => {
                return (curr.getBoundingClientRect().left > prev.getBoundingClientRect().left) ? curr : prev;
            });
            this.focusElement(closest);
        }
    }
    
    navigateRight() {
        // Para grillas: ir derecha
        this.updateFocusableElements();
        if (!this.selectedElement) {
            this.focusFirst();
            return;
        }
        
        const rect = this.selectedElement.getBoundingClientRect();
        const candidates = this.focusableElements.filter(el => {
            const elRect = el.getBoundingClientRect();
            return Math.abs(elRect.top - rect.top) < 50 && elRect.left > rect.left;
        });
        
        if (candidates.length > 0) {
            const closest = candidates.reduce((prev, curr) => {
                return (curr.getBoundingClientRect().left < prev.getBoundingClientRect().left) ? curr : prev;
            });
            this.focusElement(closest);
        }
    }
    
    focusFirst() {
        this.updateFocusableElements();
        if (this.focusableElements.length > 0) {
            this.focusElement(this.focusableElements[0]);
        }
    }
    
    focusElement(element) {
        // Remover clase anterior
        if (this.selectedElement) {
            this.selectedElement.classList.remove('gamepad-focused');
        }
        
        // Agregar clase al nuevo elemento
        this.selectedElement = element;
        element.classList.add('gamepad-focused');
        
        // Scroll si es necesario
        element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.gamepadNav = new GamepadNavigation();
    });
} else {
    window.gamepadNav = new GamepadNavigation();
}
