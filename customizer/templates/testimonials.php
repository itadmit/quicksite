<?php
/**
 * Testimonials Section Template
 * 
 * This file contains the template for rendering the testimonials section
 * in the frontend of the website.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die;
}

/**
 * Render testimonials section based on the provided data
 * 
 * @param array $section_data - Testimonials section data
 * @return string HTML content for the testimonials section
 */
function render_testimonials_section($section_data) {
    $title = $section_data['title'] ?? 'העדויות שלנו';
    $testimonials = $section_data['testimonials'] ?? [];
    
    // Start output buffer
    ob_start();
    ?>
    <section class="testimonials-section py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-3xl font-bold text-center mb-10"><?php echo htmlspecialchars($title); ?></h2>
            
            <?php if (empty($testimonials)): ?>
                <div class="empty-state bg-white p-8 rounded-lg shadow-sm text-center max-w-2xl mx-auto">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    <p class="text-gray-600 text-lg">לא נמצאו המלצות. הוסף המלצות כדי שיופיעו כאן.</p>
                </div>
            <?php else: ?>
                <div class="testimonials-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="testimonial-card bg-white rounded-lg overflow-hidden shadow-md transition-transform hover:shadow-lg hover:-translate-y-1">
                            <div class="testimonial-content p-6">
                                <p class="quote-text text-gray-600 italic mb-6 relative pr-6"><?php echo htmlspecialchars($testimonial['quote'] ?? ''); ?></p>
                                <div class="testimonial-author flex items-center">
                                    <div class="author-image w-16 h-16 rounded-full overflow-hidden mr-4 border-2 border-gray-100">
                                        <?php 
                                            $author_image = $testimonial['author_image'] ?? '';
                                            if (!empty($author_image)):
                                        ?>
                                            <img 
                                                src="<?php echo htmlspecialchars($author_image); ?>" 
                                                alt="<?php echo htmlspecialchars($testimonial['author_name'] ?? 'תמונת מחבר'); ?>"
                                                class="w-14 h-14 rounded-full object-cover"
                                                onerror="this.onerror=null; this.src='/customizer/assets/default-avatar.jpg';"
                                            >
                                        <?php else: ?>
                                            <img src="/customizer/assets/default-avatar.jpg" alt="תמונת ברירת מחדל" class="w-full h-full object-cover">
                                        <?php endif; ?>
                                    </div>
                                    <div class="author-info">
                                        <h4 class="author-name font-bold text-gray-800"><?php echo htmlspecialchars($testimonial['author_name'] ?? ''); ?></h4>
                                        <p class="author-role text-sm text-gray-500"><?php echo htmlspecialchars($testimonial['author_role'] ?? ''); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
    
    // Return the output buffer content
    return ob_get_clean();
}
?> 