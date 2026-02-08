// Tailwind Configuration
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#0FB5A9',
                secondary: '#04B6D5',
                accent: '#B3D54C',
                background: '#FAFAFA',
                foreground: '#2D2D2D',
                muted: '#F5F5F5',
                'muted-foreground': '#6B6B6B',
                border: '#E0E0E0',
                'input-background': '#F9F9F9',
                sidebar: '#FFFFFF',
                'sidebar-border': '#E8E8E8',
                'sidebar-foreground': '#4A4A4A',
                'sidebar-accent': '#F0F0F0',
                'sidebar-accent-foreground': '#2D2D2D',
                'primary-foreground': '#FFFFFF',
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            }
        }
    }
};

// Initialize Lucide Icons
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Highlight active sidebar item based on current URL
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href === currentPath || (currentPath === '' && href === 'index.html')) {
            item.classList.add('active');
            // Add active styles if not handled by CSS class alone (using Tailwind classes)
            item.classList.add('bg-primary/10', 'text-primary');
        } else {
            item.classList.remove('active', 'bg-primary/10', 'text-primary');
            item.classList.add('text-sidebar-foreground', 'hover:bg-sidebar-accent', 'hover:text-sidebar-accent-foreground');
        }
    });
});
