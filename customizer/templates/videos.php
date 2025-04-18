<?php
/**
 * Videos Section Template
 * 
 * This file contains the template for rendering the videos section
 * in the frontend of the website.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die;
}

/**
 * Render videos section based on the provided data
 * 
 * @param array $section_data - Videos section data
 * @return string HTML content for the videos section
 */
function render_videos_section($section_data) {
    $title = $section_data['title'] ?? 'הסרטונים שלנו';
    $videos = $section_data['videos'] ?? [];
    
    // Start output buffer
    ob_start();
    ?>
    <section class="videos-section py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-3xl font-bold text-center mb-10"><?php echo htmlspecialchars($title); ?></h2>
            
            <?php if (empty($videos)): ?>
                <div class="empty-state bg-white p-8 rounded-lg shadow-sm text-center max-w-2xl mx-auto">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-600 text-lg">לא נמצאו סרטונים. הוסף סרטונים כדי שיופיעו כאן.</p>
                </div>
            <?php else: ?>
                <div class="videos-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($videos as $video): 
                        // Skip invalid videos
                        if (empty($video['url']) && empty($video['embed_code'])) {
                            continue;
                        }
                    ?>
                        <div class="video-card bg-white rounded-lg overflow-hidden shadow-md transition-transform hover:shadow-lg hover:-translate-y-1">
                            <div class="video-container aspect-video relative overflow-hidden">
                                <?php if (!empty($video['embed_code'])): ?>
                                    <div class="embed-container">
                                        <?php echo $video['embed_code']; ?>
                                    </div>
                                <?php elseif (!empty($video['url'])): ?>
                                    <?php if (!empty($video['thumbnail'])): ?>
                                        <div class="video-thumbnail relative cursor-pointer" data-video-url="<?php echo htmlspecialchars($video['url']); ?>">
                                            <div class="flex-shrink-0 relative w-full md:w-64 h-40 rounded-lg overflow-hidden">
                                                <img 
                                                    src="<?php echo htmlspecialchars($video['thumbnail'] ?? ''); ?>" 
                                                    alt="<?php echo htmlspecialchars($video['title'] ?? 'תמונה ממוזערת של סרטון'); ?>" 
                                                    class="w-full h-full object-cover"
                                                    onerror="this.onerror=null; this.src='/customizer/assets/video-placeholder.jpg';"
                                                >
                                            </div>
                                            <div class="play-button absolute inset-0 flex items-center justify-center">
                                                <div class="play-icon w-16 h-16 rounded-full bg-white bg-opacity-80 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M4 4l12 6-12 6V4z" clip-rule="evenodd" fill-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <iframe 
                                            class="absolute top-0 left-0 w-full h-full"
                                            src="<?php echo htmlspecialchars($video['url']); ?>" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                        </iframe>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="video-placeholder bg-gray-200 flex items-center justify-center absolute top-0 left-0 w-full h-full">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="video-content p-5">
                                <?php if (!empty($video['title'])): ?>
                                    <h3 class="video-title text-xl font-semibold mb-2"><?php echo htmlspecialchars($video['title']); ?></h3>
                                <?php endif; ?>
                                <?php if (!empty($video['description'])): ?>
                                    <p class="video-description text-gray-600"><?php echo htmlspecialchars($video['description']); ?></p>
                                <?php endif; ?>
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