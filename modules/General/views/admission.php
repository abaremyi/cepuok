<!DOCTYPE html>
<html lang="en">

<?php
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
include_once get_layout('header');
?>

<body>

    <?php
    $home = 'off';
    $services = 'off';
    $work = 'off';
    $about = 'off';
    $news = 'off';
    $contacts = 'off';
    ?>
    
    <?php include_once get_layout('navbar'); ?>

    <!-- Page Header -->
    <header class="admission-page-header">
        <div class="container">
            <h1>Admission Information</h1>
            <p>Your journey to excellence begins here at Mount Carmel School</p>
            <div class="admission-breadcrumb">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Admission</span>
            </div>
        </div>
    </header>

    <!-- Admission Tabs Section -->
    <section class="admission-section">
        <div class="admission-container">
            
            <!-- Admission Tabs Navigation -->
            <div class="admission-tabs-nav" id="admissionTabsNav">
                <div class="tabs-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading admission information...</p>
                </div>
            </div>

            <!-- Admission Content -->
            <div class="admission-content" id="admissionContent">
                <div class="content-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading content...</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Important Dates Section -->
    <section class="dates-section">
        <div class="container">
            <div class="section-header sect-position">
                <h2>Important Admission Dates</h2>
                <p>Stay informed about our admission timelines</p>
            </div>
            <div class="dates-timeline">
                <div class="timeline-item">
                    <div class="timeline-date">January Intake</div>
                    <div class="timeline-content">
                        <h3>Application Deadline</h3>
                        <p>December 15th of previous year</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">September Intake</div>
                    <div class="timeline-content">
                        <h3>Application Deadline</h3>
                        <p>August 15th</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">Ongoing</div>
                    <div class="timeline-content">
                        <h3>School Tours</h3>
                        <p>Available Monday-Friday, 9AM-3PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Admission Office -->
    <section class="contact-admission-section">
        <div class="container">
            <div class="contact-admission-wrapper">
                <div class="contact-info">
                    <h2><i class="fas fa-headset"></i> Admission Office</h2>
                    <p>Our admission team is ready to assist you with any questions about the application process.</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Phone</h4>
                                <p>+250 789 121 680</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>admissions@mountcarmel.ac.rw</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Office Hours</h4>
                                <p>Monday - Friday: 8:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="quick-apply">
                    <h3>Ready to Apply?</h3>
                    <p>Start your application process online through our secure portal.</p>
                    <a href="https://docs.google.com/forms/d/1wogDmRr4HUKh4uqx9QpbI96s0o_EEOBoAkr2zM2k7Qw/edit" 
                       target="_blank" 
                       class="btn-apply-now">
                        <i class="fas fa-edit"></i> Start Online Application
                    </a>
                    <p class="note">Applications typically processed within 5 business days</p>
                </div>
            </div>
        </div>
    </section>

    <?php include_once get_layout('footer'); ?>
    <?php include_once get_layout('scripts'); ?>

    <script>
    const BASE_URL = '<?= url() ?>';
    const API_URL = BASE_URL + '/api/admission';
    const REGISTRATION_URL = 'https://docs.google.com/forms/d/1wogDmRr4HUKh4uqx9QpbI96s0o_EEOBoAkr2zM2k7Qw/edit';
    
    let state = {
        sections: [],
        activeSection: null,
        targetSection: null
    };

    $(document).ready(function() {
        checkUrlHash();
        loadAdmissionSections();
    });

    function checkUrlHash() {
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            state.targetSection = hash.toLowerCase();
        }
    }

    function loadAdmissionSections() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_all_sections' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    state.sections = response.data;
                    displayAdmissionTabs(response.data);
                    
                    if (state.targetSection) {
                        const targetIndex = response.data.findIndex(s => 
                            s.slug === state.targetSection || 
                            s.title.toLowerCase().replace(/\s+/g, '-') === state.targetSection
                        );
                        if (targetIndex !== -1) {
                            setTimeout(() => {
                                activateTab(targetIndex, true);
                            }, 300);
                        } else {
                            activateTab(0);
                        }
                    } else {
                        activateTab(0);
                    }
                } else {
                    showError('No admission information available');
                }
            },
            error: function(xhr, status, error) {
                console.error('Admission API Error:', error);
                showError('Failed to load admission information');
            }
        });
    }

    function displayAdmissionTabs(sections) {
        let tabsHtml = '';
        
        sections.forEach((section, index) => {
            const slug = section.slug || section.title.toLowerCase().replace(/\s+/g, '-');
            tabsHtml += `
                <button class="admission-tab" data-index="${index}" data-slug="${slug}">
                    <div class="tab-icon">
                        <i class="${section.icon_class || 'fas fa-info-circle'}"></i>
                    </div>
                    <div class="tab-info">
                        <h3>${section.title}</h3>
                        <p>${section.subtitle || ''}</p>
                    </div>
                </button>
            `;
        });
        
        $('#admissionTabsNav').html(tabsHtml);
        
        $('.admission-tab').click(function() {
            const index = $(this).data('index');
            activateTab(index, true);
        });
    }

    function activateTab(index, smooth = false) {
        const section = state.sections[index];
        if (!section) return;
        
        $('.admission-tab').removeClass('active');
        $(`.admission-tab[data-index="${index}"]`).addClass('active');
        
        state.activeSection = section;
        
        if (smooth) {
            $('#admissionContent').fadeOut(200, function() {
                displaySectionContent(section);
                $('#admissionContent').fadeIn(400);
                
                $('html, body').animate({
                    scrollTop: $('#admissionContent').offset().top - 100
                }, 600, 'easeInOutCubic');
            });
        } else {
            displaySectionContent(section);
        }
        
        const slug = section.slug || section.title.toLowerCase().replace(/\s+/g, '-');
        history.replaceState(null, null, `#${slug}`);
    }

    function displaySectionContent(section) {
        let html = '';
        
        if (section.title.toLowerCase().includes('online registration')) {
            // Special handling for registration section
            html = `
                <div class="section-detail registration-section">
                    <div class="section-header">
                        <span class="section-label">ADMISSION PROCESS</span>
                        <h2 class="section-title">${section.title}</h2>
                        ${section.subtitle ? `<h3 class="section-subtitle">${section.subtitle}</h3>` : ''}
                    </div>
                    
                    <div class="registration-info">
                        <div class="info-box">
                            <div class="info-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <h4>Online Application Portal</h4>
                            <p>Complete your application securely through our online portal</p>
                            <a href="${REGISTRATION_URL}" target="_blank" class="btn-register">
                                <i class="fas fa-external-link-alt"></i> Access Application Form
                            </a>
                        </div>
                        
                        <div class="info-box">
                            <div class="info-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4>Mobile Friendly</h4>
                            <p>Accessible on all devices - complete your application anywhere</p>
                        </div>
                        
                        <div class="info-box">
                            <div class="info-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4>Secure & Private</h4>
                            <p>Your information is protected with industry-standard encryption</p>
                        </div>
                    </div>
                    
                    ${section.content && section.content.length > 0 ? `
                    <div class="content-items">
                        ${section.content.map(item => {
                            let contentHtml = '';
                            if (item.formatted_content) {
                                contentHtml = item.formatted_content;
                            } else if (item.content) {
                                if (typeof item.content === 'string') {
                                    // Try to parse JSON string
                                    try {
                                        const parsed = JSON.parse(item.content);
                                        if (typeof parsed === 'object') {
                                            contentHtml = Object.entries(parsed).map(([key, value]) => {
                                                if (Array.isArray(value)) {
                                                    return `<div class="info-item"><strong>${key.replace(/_/g, ' ')}:</strong><ul>${value.map(v => `<li>${v}</li>`).join('')}</ul></div>`;
                                                } else {
                                                    return `<div class="info-item"><strong>${key.replace(/_/g, ' ')}:</strong> ${value}</div>`;
                                                }
                                            }).join('');
                                        } else {
                                            contentHtml = item.content;
                                        }
                                    } catch (e) {
                                        contentHtml = item.content;
                                    }
                                } else if (typeof item.content === 'object') {
                                    // Handle object content
                                    contentHtml = Object.entries(item.content).map(([key, value]) => {
                                        if (Array.isArray(value)) {
                                            return `<div class="info-item"><strong>${key.replace(/_/g, ' ')}:</strong><ul>${value.map(v => `<li>${v}</li>`).join('')}</ul></div>`;
                                        } else {
                                            return `<div class="info-item"><strong>${key.replace(/_/g, ' ')}:</strong> ${value}</div>`;
                                        }
                                    }).join('');
                                }
                            }
                            return `
                            <div class="content-item">
                                <h4><i class="${item.icon || 'fas fa-circle'}"></i> ${item.title}</h4>
                                <div class="item-content">${contentHtml}</div>
                            </div>
                            `;
                        }).join('')}
                    </div>
                    ` : ''}
                    
                    <div class="application-tips">
                        <h4><i class="fas fa-lightbulb"></i> Application Tips</h4>
                        <ul>
                            <li>Have all required documents ready before starting</li>
                            <li>Use a valid email address for communication</li>
                            <li>Save your progress if you need to complete later</li>
                            <li>Keep a copy of your application reference number</li>
                        </ul>
                    </div>
                </div>
            `;
        } else {
            // Regular content display for requirements and fees
            html = `
                <div class="section-detail">
                    <div class="section-header">
                        <span class="section-label">ADMISSION INFORMATION</span>
                        <h2 class="section-title">${section.title}</h2>
                        ${section.subtitle ? `<h3 class="section-subtitle">${section.subtitle}</h3>` : ''}
                    </div>
                    
                    ${section.content && section.content.length > 0 ? `
                    <div class="content-items">
                        ${section.content.map(item => {
                            let contentHtml = '';
                            if (item.formatted_content) {
                                contentHtml = item.formatted_content;
                            } else if (item.content) {
                                if (typeof item.content === 'string') {
                                    // Try to parse JSON string
                                    try {
                                        const parsed = JSON.parse(item.content);
                                        if (typeof parsed === 'object') {
                                            contentHtml = Object.entries(parsed).map(([key, value]) => {
                                                if (Array.isArray(value)) {
                                                    return `<div class="info-item"><strong>${key.replace(/_/g, ' ')}:</strong><ul>${value.map(v => `<li>${v}</li>`).join('')}</ul></div>`;
                                                } else {
                                                    return `<div class="info-item"><strong>${key.replace(/_/g, ' ')}:</strong> ${value}</div>`;
                                                }
                                            }).join('');
                                        } else {
                                            contentHtml = item.content;
                                        }
                                    } catch (e) {
                                        contentHtml = item.content;
                                    }
                                } else if (typeof item.content === 'object') {
                                    // Handle object content
                                    contentHtml = Object.entries(item.content).map(([key, value]) => {
                                        if (Array.isArray(value)) {
                                            return `<div class="info-item"><strong>${key.replace(/_/g, ' ')}:</strong><ul>${value.map(v => `<li>${v}</li>`).join('')}</ul></div>`;
                                        } else {
                                            return `<div class="info-item"><strong>${key.replace(/_/g, ' ')}:</strong> ${value}</div>`;
                                        }
                                    }).join('');
                                }
                            }
                            let documentsHtml = '';
                            if (item.documents && item.documents.length > 0) {
                                documentsHtml = `
                                    <div class="documents-section mt-4">
                                        <h5><i class="fas fa-download me-2"></i>Downloadable Documents</h5>
                                        <div class="documents-grid">
                                            ${item.documents.map(doc => {
                                                const fileIcon = getFileIcon(doc.file_type);
                                                const fileSize = formatFileSize(doc.file_size);
                                                return `
                                                    <div class="document-card">
                                                        <div class="document-icon">
                                                            <i class="${fileIcon}"></i>
                                                        </div>
                                                        <div class="document-info">
                                                            <h6>${doc.title}</h6>
                                                            <p class="text-muted small">${doc.description || ''}</p>
                                                            <div class="document-meta">
                                                                <span><i class="fas fa-file"></i> ${doc.file_type}</span>
                                                                <span><i class="fas fa-download"></i> ${doc.downloads || 0}</span>
                                                                <span><i class="fas fa-hdd"></i> ${fileSize}</span>
                                                            </div>
                                                        </div>
                                                        <a href="${BASE_URL}${doc.file_url}" 
                                                        target="_blank" 
                                                        class="btn-download"
                                                        onclick="trackDownload(${doc.id})">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                `;
                            }
                            
                            return `
                                <div class="content-item">
                                    <div class="item-header">
                                        <div class="item-icon">
                                            <i class="${item.icon || 'fas fa-circle'}"></i>
                                        </div>
                                        <h4>${item.title}</h4>
                                    </div>
                                    <div class="item-content">${contentHtml}</div>
                                    ${documentsHtml}
                                    ${item.metadata ? `
                                    <div class="item-metadata">
                                        ${Object.entries(item.metadata).map(([key, value]) => `
                                        <span class="meta-tag"><strong>${key.replace('_', ' ')}:</strong> ${value}</span>
                                        `).join('')}
                                    </div>
                                    ` : ''}
                                </div>
                                `;
                            }).join('')}
                        </div>
                        ` : '<p class="no-content">No content available for this section.</p>'}
                        
                        <!-- Section Documents -->
                        ${section.documents && section.documents.length > 0 ? `
                        <div class="section-documents mt-5">
                            <div class="section-header">
                                <h3><i class="fas fa-paperclip me-2"></i>Supporting Documents</h3>
                                <p>Download additional resources for ${section.title}</p>
                            </div>
                            <div class="documents-grid">
                                ${section.documents.map(doc => {
                                    const fileIcon = getFileIcon(doc.file_type);
                                    const fileSize = formatFileSize(doc.file_size);
                                    return `
                                        <div class="document-card">
                                            <div class="document-icon">
                                                <i class="${fileIcon}"></i>
                                            </div>
                                            <div class="document-info">
                                                <h5>${doc.title}</h5>
                                                <p>${doc.description || ''}</p>
                                                <div class="document-meta">
                                                    <span><i class="fas fa-file"></i> ${doc.file_type}</span>
                                                    <span><i class="fas fa-download"></i> ${doc.downloads || 0}</span>
                                                    <span><i class="fas fa-hdd"></i> ${fileSize}</span>
                                                </div>
                                            </div>
                                            <a href="${BASE_URL}${doc.file_url}" 
                                            target="_blank" 
                                            class="btn-download"
                                            onclick="trackDownload(${doc.id})">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                        ` : ''}
                        
                        ${section.title.toLowerCase().includes('fee') ? `
                        <div class="payment-options">
                            <h4><i class="fas fa-credit-card"></i> Payment Options</h4>
                            <div class="options-grid">
                                <div class="option">
                                    <i class="fas fa-bank"></i>
                                    <h5>Bank Transfer</h5>
                                    <p>Direct transfer to school account</p>
                                </div>
                                <div class="option">
                                    <i class="fas fa-mobile-alt"></i>
                                    <h5>Mobile Money</h5>
                                    <p>MTN Mobile Money & Airtel Money</p>
                                </div>
                                <div class="option">
                                    <i class="fas fa-cash-register"></i>
                                    <h5>Cash Payment</h5>
                                    <p>At school finance office</p>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
        }
        
        $('#admissionContent').html(html);
    }

    function showError(message) {
        $('#admissionTabsNav').html(`
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
            </div>
        `);
        $('#admissionContent').html(`
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
            </div>
        `);
    }

    // Helper functions for file handling
    function getFileIcon(fileType) {
        const icons = {
            'PDF': 'fas fa-file-pdf text-danger',
            'Word': 'fas fa-file-word text-primary',
            'Excel': 'fas fa-file-excel text-success',
            'Image': 'fas fa-file-image text-info'
        };
        return icons[fileType] || 'fas fa-file text-secondary';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function trackDownload(documentId) {
        // Send AJAX request to track download
        $.ajax({
            url: API_URL,
            method: 'POST',
            data: {
                action: 'track_download',
                document_id: documentId
            },
            dataType: 'json'
        }).fail(function(error) {
            console.error('Failed to track download:', error);
        });
    }

    // Smooth scroll easing
    $.easing.easeInOutCubic = function(x, t, b, c, d) {
        if ((t/=d/2) < 1) return c/2*t*t*t + b;
        return c/2*((t-=2)*t*t + 2) + b;
    };
    </script>

    <style>
        /* Admission Page Header */
        .admission-page-header {
            background: linear-gradient(135deg, rgba(0, 121, 107, 0.9), rgba(26, 58, 82, 0.9)),
                        url('<?= img_url("hero-admission.jpg") ?>');
            color: white;
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
            background-size: cover;
            overflow: hidden;
        }

        .admission-page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect fill="rgba(255,255,255,0.05)" width="50" height="50"/></svg>');
            opacity: 0.1;
        }

        .admission-page-header .container {
            position: relative;
            z-index: 1;
        }

        .admission-page-header h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            animation: fadeInDown 0.8s ease;
        }

        .admission-page-header p {
            font-size: 1.3rem;
            opacity: 0.95;
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .admission-breadcrumb {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            font-size: 1rem;
            animation: fadeIn 0.8s ease 0.4s both;
        }

        .admission-breadcrumb a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .admission-breadcrumb a:hover {
            opacity: 0.8;
        }

        /* Admission Section */
        .admission-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .admission-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Admission Tabs Navigation */
        .admission-tabs-nav {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 3rem;
            overflow-x: auto;
            padding: 10px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .admission-tab {
            flex: 1;
            min-width: 250px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.8rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .admission-tab:hover {
            border-color: #00796B;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 121, 107, 0.15);
        }

        .admission-tab.active {
            background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
            border-color: #00796B;
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 121, 107, 0.3);
        }

        .tab-icon {
            width: 60px;
            height: 60px;
            background: rgba(0, 121, 107, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #00796B;
            transition: all 0.3s;
        }

        .admission-tab.active .tab-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .tab-info h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: #2c3e50;
            transition: color 0.3s;
        }

        .admission-tab.active .tab-info h3 {
            color: white;
        }

        .tab-info p {
            font-size: 0.95rem;
            color: #7f8c8d;
            margin: 0;
            transition: color 0.3s;
        }

        .admission-tab.active .tab-info p {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Admission Content */
        .admission-content {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .section-detail {
            padding: 3rem;
        }

        .registration-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .section-header {
            margin-bottom: 2.5rem;
        }

        .section-label {
            color: #00796B;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 0.8rem;
            display: block;
            text-align: center;
            letter-spacing: 15px;
        }

        .section-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #00796B;
            font-weight: 500;
            margin-bottom: 1.5rem;
            text-align: center;
            letter-spacing: 2px;
        }

        .content-items {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .content-item {
            background: #f8f9fa;
            border-radius: 2px;
            padding: 2rem;
            border: 2px solid #00796B;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .item-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .item-icon {
            width: 50px;
            height: 50px;
            background: #00796B;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .content-item h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .item-content {
            font-size: 1.1rem;
            color: #555;
            line-height: 1.8;
        }

        .item-content .info-item,
        .item-content .fee-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .item-content .info-item:last-child,
        .item-content .fee-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .item-content strong {
            color: #00796B;
            display: inline-block;
            min-width: 200px;
        }

        .item-content ul,
        .item-content ol {
            margin: 0.5rem 0 0 1.5rem;
            padding: 0;
        }

        .item-content li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .item-metadata {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .meta-tag {
            background: #e3f2fd;
            color: #1565c0;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .meta-tag strong {
            color: #0d47a1;
        }

        /* Registration Info Boxes */
        .registration-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .info-box {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .info-box:hover {
            transform: translateY(-5px);
        }

        .info-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .info-box h4 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .info-box p {
            color: #7f8c8d;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .btn-register {
            display: inline-block;
            background: #00796B;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background: #004D40;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 121, 107, 0.3);
        }

        /* Application Tips */
        .application-tips {
            background: #fff8e1;
            border-radius: 2px;
            padding: 2rem;
            border: 2px solid #ffb300;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .application-tips h4 {
            color: #5d4037;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .application-tips ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .application-tips li {
            color: #5d4037;
            margin-bottom: 0.8rem;
            line-height: 1.6;
        }

        /* Payment Options */
        .payment-options {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .payment-options h4 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .option {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .option i {
            font-size: 2.5rem;
            color: #00796B;
            margin-bottom: 1rem;
        }

        .option h5 {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .option p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Important Dates Section */
        .dates-section {
            padding: 80px 0;
            background: white;
        }

        .dates-timeline {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .dates-timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #00796B;
        }

        .timeline-item {
            position: relative;
            padding-left: 80px;
            margin-bottom: 3rem;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-date {
            position: absolute;
            left: 0;
            top: 0;
            width: 70px;
            height: 70px;
            background: #00796B;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            line-height: 1.2;
            padding: 5px;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid #b2c5c2ff;
        }

        .timeline-content h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .timeline-content p {
            color: #7f8c8d;
            margin: 0;
        }

        /* Contact Admission Section */
        .contact-admission-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .contact-admission-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-info h2 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .contact-info p {
            color: #7f8c8d;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .contact-details {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .contact-item i {
            width: 40px;
            height: 40px;
            background: #00796B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .contact-item h4 {
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }

        .contact-item p {
            color: #7f8c8d;
            margin: 0;
        }

        .quick-apply {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .quick-apply h3 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .quick-apply p {
            color: #7f8c8d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-apply-now {
            display: inline-block;
            background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .btn-apply-now:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 121, 107, 0.4);
        }

        .note {
            font-size: 0.9rem;
            color: #999;
            margin-top: 1rem;
        }

        .sect-position>h2{
            font-size: 2.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1.2;
            text-align: center;
        }
        .sect-position>p {
            font-size: 1.0rem;
            color: #00796B;
            font-weight: 500;
            margin-bottom: 1.5rem;
            text-align: center;
            letter-spacing: 2px;
        }

        /* Loading States */
        .tabs-loading,
        .content-loading,
        .error-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem;
            text-align: center;
        }

        .tabs-loading i,
        .content-loading i,
        .error-state i {
            font-size: 3rem;
            color: #00796B;
            margin-bottom: 1rem;
        }

        .tabs-loading p,
        .content-loading p,
        .error-state p {
            font-size: 1.1rem;
            color: #7f8c8d;
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .admission-tabs-nav {
                flex-direction: column;
            }

            .admission-tab {
                min-width: 100%;
            }

            .contact-admission-wrapper {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .section-title {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            .admission-page-header h1 {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .section-subtitle {
                font-size: 1.2rem;
            }

            .section-detail {
                padding: 2rem;
            }

            .registration-info {
                grid-template-columns: 1fr;
            }

            .dates-timeline::before {
                left: 25px;
            }

            .timeline-item {
                padding-left: 60px;
            }

            .timeline-date {
                width: 50px;
                height: 50px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .admission-page-header {
                padding: 80px 0 60px;
            }

            .admission-page-header h1 {
                font-size: 2rem;
            }

            .options-grid {
                grid-template-columns: 1fr;
            }

            .item-content strong {
                min-width: auto;
                display: block;
                margin-bottom: 0.5rem;
            }
        }

        /* Documents Section */
        .section-documents {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 2.5rem;
            margin-top: 3rem;
            border: 2px solid #e0e0e0;
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .document-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }

        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            border-color: #00796B;
        }

        .document-icon {
            width: 60px;
            height: 60px;
            background: #f0f7ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .document-info {
            flex: 1;
        }

        .document-info h5,
        .document-info h6 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
            font-weight: 600;
        }

        .document-info p {
            color: #7f8c8d;
            margin: 0 0 1rem 0;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .document-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.8rem;
            color: #95a5a6;
        }

        .document-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-download {
            background: #00796B;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.6rem 1.2rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            font-weight: 500;
            white-space: nowrap;
        }

        .btn-download:hover {
            background: #004D40;
            color: white;
            transform: translateY(-2px);
        }

        .documents-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }

        .documents-section h5 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .documents-grid {
                grid-template-columns: 1fr;
            }
            
            .document-card {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .document-meta {
                justify-content: center;
                flex-wrap: wrap;
                gap: 1rem;
            }
        }
    </style>

</body>
</html>