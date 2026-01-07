<div class="card-body">
    <!-- Debug output can be enabled for testing -->
    <?php //echo '<pre>'; print_r($sections); echo '</pre>';?>

    <?php if (count($sections) > 0): ?>
        <!-- First clear any existing section displays in case there's a double include issue -->
        <div id="sections-container">
            <?php foreach ($sections as $index => $section): ?>
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">
                        Section <?php echo $section['section_order']; ?>: 
                        <?php echo !empty($section['title']) ? htmlspecialchars($section['title']) : 'Untitled'; ?>
                    </h6>
                    <div>
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editSectionModal<?php echo $section['id']; ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteSectionModal<?php echo $section['id']; ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?php if (!empty($section['title'])): ?>
                            <h5><?php echo htmlspecialchars($section['title']); ?></h5>
                            <?php endif; ?>
                            <div class="mb-3">
                                <?php echo nl2br(htmlspecialchars($section['content'])); ?>
                            </div>
                            
                            <?php if (!empty($section['features'])): ?>
                            <h6 class="font-weight-bold">Features/Bullet Points:</h6>
                            <ul>
                                <?php foreach ($section['features'] as $feature): ?>
                                <li><?php echo htmlspecialchars($feature['feature_text']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <?php if ($section['image_path']): ?>
                            <img src="<?php echo '../../../assets/image/' . htmlspecialchars($section['image_path']); ?>" 
                                 class="img-fluid img-thumbnail" alt="Section image">
                            <?php else: ?>
                            <div class="alert alert-secondary">No image uploaded</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Put all modals outside the main display container -->
        <?php foreach ($sections as $section): ?>
        <!-- Edit Section Modal -->
        <div class="modal fade" id="editSectionModal<?php echo $section['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editSectionModalLabel<?php echo $section['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_section">
                        <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editSectionModalLabel<?php echo $section['id']; ?>">
                                Edit Section <?php echo $section['section_order']; ?>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="section_title<?php echo $section['id']; ?>">Section Title (Optional)</label>
                                <input type="text" class="form-control" id="section_title<?php echo $section['id']; ?>" 
                                       name="section_title" value="<?php echo htmlspecialchars($section['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="section_content<?php echo $section['id']; ?>">Content</label>
                                <textarea class="form-control" id="section_content<?php echo $section['id']; ?>" 
                                          name="section_content" rows="6" required><?php echo htmlspecialchars($section['content']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="section_image<?php echo $section['id']; ?>">Section Image</label>
                                <?php if ($section['image_path']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo '../../../assets/image/' . htmlspecialchars($section['image_path']); ?>" 
                                         class="img-fluid img-thumbnail" alt="Current image" style="max-height: 150px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control-file" id="section_image<?php echo $section['id']; ?>" 
                                       name="section_image">
                                <small class="form-text text-muted">Leave empty to keep current image. Accepted formats: JPG, JPEG, PNG, GIF, WEBP.</small>
                            </div>
                            
                            <!-- Features (Bullet Points) Section -->
                            <?php if (in_array($currentPage, ['why', 'how'])): ?>
                            <div class="form-group">
                                <label>Features/Bullet Points</label>
                                <div id="features-container<?php echo $section['id']; ?>">
                                    <?php if (!empty($section['features'])): ?>
                                        <?php foreach ($section['features'] as $feature): ?>
                                        <div class="input-group mb-2 feature-item">
                                            <input type="hidden" name="feature_id[]" value="<?php echo $feature['id']; ?>">
                                            <input type="text" class="form-control" name="feature_text[]" 
                                                   value="<?php echo htmlspecialchars($feature['feature_text']); ?>">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-feature">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary add-feature" 
                                        data-container="features-container<?php echo $section['id']; ?>">
                                    <i class="fas fa-plus"></i> Add Feature
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Section Modal -->
        <div class="modal fade" id="deleteSectionModal<?php echo $section['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteSectionModalLabel<?php echo $section['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="" method="post">
                        <input type="hidden" name="action" value="delete_section">
                        <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                        <input type="hidden" name="content_id" value="<?php echo $pageData['id']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteSectionModalLabel<?php echo $section['id']; ?>">Confirm Delete</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this section?</p>
                            <p class="text-danger">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            No sections found. Click the "Add New Section" button to add content to this page.
        </div>
    <?php endif; ?>
</div>