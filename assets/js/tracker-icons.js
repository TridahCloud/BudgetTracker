// Tracker Icon Suggestions
// Users can pick from these when creating trackers

const trackerIcons = {
    personal: ['💰', '💵', '💳', '🏠', '👤', '📊'],
    business: ['💼', '🏢', '📈', '💻', '🏪', '🚀'],
    family: ['👨‍👩‍👧‍👦', '👪', '🏡', '❤️', '🍼', '🎓'],
    savings: ['🐷', '💎', '🎯', '⭐', '🏆', '💡'],
    investment: ['📈', '💹', '💎', '🔮', '🎲', '🌟'],
    other: ['📌', '⚡', '🎨', '🔧', '🎁', '🌈']
};

const trackerColors = [
    '#6366f1', // Blue
    '#10b981', // Green
    '#f59e0b', // Orange
    '#ef4444', // Red
    '#8b5cf6', // Purple
    '#ec4899', // Pink
    '#14b8a6', // Teal
    '#f97316', // Orange Red
    '#06b6d4', // Cyan
    '#84cc16'  // Lime
];

// Suggestion function for UI
function getIconSuggestions(type) {
    return trackerIcons[type] || trackerIcons.other;
}

function getRandomColor() {
    return trackerColors[Math.floor(Math.random() * trackerColors.length)];
}

