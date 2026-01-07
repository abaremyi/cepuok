<?php
require_once '../../../helpers/JWTHandler.php'; // Include your JWT handler

// Start the session or read the cookie
if (!isset($_COOKIE['jwtToken'])) {
    // If the cookie is not set, redirect the user to the login page
    // echo "<h2>If the cookie is not set, redirect the user to the login page</h2>";
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// Retrieve the token from the cookie
$jwtToken = $_COOKIE['jwtToken'];

// Initialize JWT handler
$jwtHandler = new JWTHandler();

// Validate the token
$decodedToken = $jwtHandler->validateToken($jwtToken);

if ($decodedToken === false) {
    // If the token is invalid, redirect to login page
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// if ($decodedToken->role != 1) {
//     echo "<h2>Access Denied</h2>";
//     exit;
// }

// If the token is valid, proceed to display the dashboard
// echo "<h2>Welcome to the Dashboard, {$decodedToken->username}</h2>";
?>

<!DOCTYPE html>
<html lang="en">

<?php include('../../../layouts/admin_header.php'); ?>

<body id="page-top">
<style>
        
    </style>
    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include('../../../layouts/admin_sidebar.php'); ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include('../../../layouts/admin_navbar.php'); ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h4 class="h5 mb-0 text-gray-800">Messages Management Page</h4>

                    </div> -->
                    <!-- Content Row -->

                    <div class="mailbox-wrapper">
                        <!-- Content Header (Page header) -->
                        <section class="mailbox-header">
                            <div>
                                <h1>
                                    Mailbox
                                    <small id="new-messages-count">13 new messages</small>
                                </h1>
                                <ol class="mailbox-breadcrumb">
                                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                                    <li class="active">Mailbox</li>
                                </ol>
                            </div>
                        </section>
                
                        <!-- Main content -->
                        <section class="mailbox-main">
                            <div class="mailbox-row">
                                <div class="mailbox-sidebar">
                                    <div class="mailbox-box">
                                        <div class="mailbox-box-header">
                                            <h3 class="mailbox-box-title">Folders</h3>
                                        </div>
                                        <div class="mailbox-box-body no-padding">
                                            <ul class="mailbox-nav">
                                                <li class="mailbox-nav-item active">
                                                    <a href="#" class="mailbox-nav-link mailbox-folder" data-status="">
                                                        <i class="fa fa-inbox"></i> &nbsp; Inbox 
                                                        <span class="mailbox-label mailbox-label-primary mailbox-pull-right" id="inbox-count">12</span>
                                                    </a>
                                                </li>
                                                <li class="mailbox-nav-item">
                                                    <a href="#" class="mailbox-nav-link mailbox-folder" data-status="starred">
                                                        <i class="bx bx-star"></i> &nbsp; Starred
                                                    </a>
                                                </li>
                                                <li class="mailbox-nav-item">
                                                    <a href="#" class="mailbox-nav-link mailbox-folder" data-status="trash">
                                                        <i class="bx bxs-trash"></i> &nbsp; Trash
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mailbox-messages-container">
                                    <div class="mailbox-box mailbox-box-primary">
                                        <div class="mailbox-box-header">
                                            <h3 class="mailbox-box-title">Inbox</h3>
                                            <div class="mailbox-pull-right mailbox-box-tools">
                                                <div class="mailbox-has-feedback">
                                                    <input type="text" class="mailbox-form-control mailbox-input-sm" id="search-mail" placeholder="Search Mail" />
                                                    <span class="fa fa-search mailbox-form-control-feedback"></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mailbox-box-body no-padding">
                                            <div class="mailbox-controls">
                                                <!-- Check all button -->
                                                <div class="mailbox-btn-group">
                                                    <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm checkbox-toggle" style="display:flex;align-items: center;"><i class="bx bx-square" style="font-size:14px;"></i></button>
                                                    <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm" id="delete-selected"><i class="fa fa-trash"></i></button>
                                                    <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm" id="refresh-mail"><i class="fa fa-spinner"></i></button>
                                                </div>
                                                <div class="mailbox-pull-right">
                                                    <span id="pagination-info">1-20/200</span>
                                                    <div class="mailbox-btn-group">
                                                        <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm" id="prev-page"><i class="fa fa-chevron-left"></i></button>
                                                        <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm" id="next-page"><i class="fa fa-chevron-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mailbox-table-responsive">
                                                <table class="mailbox-table mailbox-table-hover mailbox-table-striped" id="mailbox-messages">
                                                    <tbody>
                                                        <tr>
                                                            <td><input type="checkbox" data-id="1"></td>
                                                            <td class="mailbox-star"><a href="#" class="star-message" data-id="1"><i class="fa fa-star-o mailbox-star-o"></i></a></td>
                                                            <td class="mailbox-name">Herbert S.</td>
                                                            <td class="mailbox-subject">Website Development - Just wanted to ask if you're interested in getting a new website made or need some changes to your existing one? We have...</td>
                                                            <td class="mailbox-date">5 months ago</td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="checkbox" data-id="2"></td>
                                                            <td class="mailbox-star"><a href="#" class="star-message" data-id="2"><i class="fa fa-star-o mailbox-star-o"></i></a></td>
                                                            <td class="mailbox-name">James P</td>
                                                            <td class="mailbox-subject">Design Work - Do you need help with graphic design - brochures, banners, flyers, advertisements, social media posts, logos etc? We charge a low fixed monthly...</td>
                                                            <td class="mailbox-date">6 months ago</td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="checkbox" data-id="3"></td>
                                                            <td class="mailbox-star"><a href="#" class="star-message" data-id="3"><i class="fa fa-star-o mailbox-star-o"></i></a></td>
                                                            <td class="mailbox-name">James P</td>
                                                            <td class="mailbox-subject">Design Work - Do you need help with graphic design - brochures, banners, flyers, advertisements, social media posts, logos etc? We charge a low fixed monthly...</td>
                                                            <td class="mailbox-date">6 months ago</td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="checkbox" data-id="4"></td>
                                                            <td class="mailbox-star"><a href="#" class="star-message" data-id="4"><i class="fa fa-star-o mailbox-star-o"></i></a></td>
                                                            <td class="mailbox-name">Nina Tesfamariam</td>
                                                            <td class="mailbox-subject">essential oils - nnnnn</td>
                                                            <td class="mailbox-date">7 months ago</td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="checkbox" data-id="5"></td>
                                                            <td class="mailbox-star"><a href="#" class="star-message" data-id="5"><i class="fa fa-star-o mailbox-star-o"></i></a></td>
                                                            <td class="mailbox-name">Lilly</td>
                                                            <td class="mailbox-subject">appreciation - thank you</td>
                                                            <td class="mailbox-date">7 months ago</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <div class="mailbox-box-footer no-padding">
                                            <div class="mailbox-controls">
                                                <div class="mailbox-btn-group">
                                                    <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm checkbox-toggle" style="display:flex;align-items: center;"><i class="bx bx-square" style="font-size:14px;"></i></button>
                                                    <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm"><i class="fa fa-trash"></i></button>
                                                    <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm"><i class="fa fa-spinner"></i></button>
                                                </div>
                                                <div class="mailbox-pull-right">
                                                    <span id="pagination-info2">1-20/200</span>
                                                    <div class="mailbox-btn-group">
                                                        <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm" id="prev-page2"><i class="fa fa-chevron-left"></i></button>
                                                        <button class="mailbox-btn mailbox-btn-default mailbox-btn-sm" id="next-page2"><i class="fa fa-chevron-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                
                        <!-- Delete Confirmation Modal -->
                        <div id="deleteConfirmationModal" class="mailbox-modal">
                            <div class="mailbox-modal-dialog">
                                <div class="mailbox-modal-content">
                                    <div class="mailbox-modal-header">
                                        <h5 class="mailbox-modal-title">Confirm Deletion</h5>
                                        <button type="button" class="mailbox-close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="mailbox-modal-body mailbox-text-center">
                                        Are you sure you want to delete the selected messages?
                                        <br>
                                        <small>(If a message is not already in trash, it will be moved to trash first)</small>
                                    </div>
                                    <div class="mailbox-modal-footer mailbox-text-center">
                                        <button type="button" class="mailbox-btn mailbox-bg-maroon" id="confirm-delete"><i class="fa fa-eraser"></i> Permanently Delete</button>
                                        <button type="button" class="mailbox-btn mailbox-bg-purple" id="confirm-trash"><i class="fa fa-trash-o"></i> Trash</button>
                                        <button type="button" class="mailbox-btn mailbox-btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.row -->
                        <!-- /.content row -->
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; EVERRETREAT <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="../../Authentication/views/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Dashboard Scripts -->
    <?php include('../../../layouts/admin_scripts.php'); ?>
    <script>
        $(document).ready(function() {
            // Initial variables
            let currentPage = 1;
            let currentStatus = '';
            let currentSearch = '';
            
            // Initial load of messages
            fetchMessages();
            
            // Function to fetch messages from the API
            function fetchMessages(status = currentStatus, page = 1, search = currentSearch) {
                // Save current parameters
                currentStatus = status;
                currentPage = page;
                currentSearch = search;
                
                // Show loading spinner
                $('#mailbox-messages tbody').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading messages...</td></tr>');
                
                // Make AJAX request to the API
                $.ajax({
                    url: '../../Contactus/api/messagesApi.php',
                    type: 'GET',
                    data: {
                        action: 'get_messages',
                        status: status,
                        page: page,
                        search: search
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update messages table
                            updateMessagesTable(response.messages);
                            
                            // Update pagination
                            updatePagination(response.pagination);
                            
                            // Update new messages count
                            $('#new-messages-count').text(response.pagination.new_messages + ' new messages');
                            $('#inbox-count').text(response.pagination.new_messages);
                            
                            // Update folder title
                            let folderTitle = 'Inbox';
                            if (status === 'starred') folderTitle = 'Starred';
                            if (status === 'trash') folderTitle = 'Trash';
                            $('.mailbox-box-title').eq(1).text(folderTitle);
                        } else {
                            // Show error message
                            $('#mailbox-messages tbody').html('<tr><td colspan="5" class="text-center">Error loading messages</td></tr>');
                        }
                    },
                    error: function() {
                        // Show error message
                        $('#mailbox-messages tbody').html('<tr><td colspan="5" class="text-center">Error connecting to server</td></tr>');
                    }
                });
            }
            
            // Function to update messages table
            function updateMessagesTable(messages) {
                let tableContent = '';
                
                if (messages.length === 0) {
                    tableContent = '<tr><td colspan="5" class="text-center">No messages found</td></tr>';
                } else {
                    messages.forEach(function(message) {
                        const isStarred = message.is_starred == 1;
                        const starClass = isStarred ? 'bxs-star mailbox-star' : 'bx-star mailbox-star-o';
                        const isNew = message.status === 'New';
                        const rowClass = isNew ? 'font-weight-bold' : '';
                        
                        tableContent += `
                        <tr class="${rowClass}">
                            <td><input type="checkbox" data-id="${message.id}"></td>
                            <td class="mailbox-star"><a href="#" class="star-message" data-id="${message.id}"><i class="bx ${starClass}"></i></a></td>
                            <td class="mailbox-name">${message.names}</td>
                            <td class="mailbox-subject">${message.subject_preview} - ${message.message_preview}</td>
                            <td class="mailbox-date">${message.time_ago}</td>
                        </tr>
                        `;
                    });
                }
                
                $('#mailbox-messages tbody').html(tableContent);
            }
            
            // Function to update pagination
            function updatePagination(pagination) {
                const paginationText = `${pagination.start_index}-${pagination.end_index}/${pagination.total}`;
                $('#pagination-info, #pagination-info2').text(paginationText);
                
                // Enable/disable pagination buttons
                $('#prev-page, #prev-page2').prop('disabled', pagination.current_page <= 1);
                $('#next-page, #next-page2').prop('disabled', pagination.current_page >= pagination.total_pages);
            }
            
            // Handle folder clicks
            $('.mailbox-folder').on('click', function(e) {
                e.preventDefault();
                const status = $(this).data('status');
                $('.mailbox-nav-item').removeClass('active');
                $(this).parent().addClass('active');
                fetchMessages(status, 1, currentSearch);
            });
            
            // Handle search
            $('#search-mail').on('input', function() {
                const searchQuery = $(this).val();
                if (searchQuery.length >= 3 || searchQuery.length === 0) {
                    // Only search if query is 3+ characters or empty (show all)
                    fetchMessages(currentStatus, 1, searchQuery);
                }
            });
            
            // Check all
            $('.checkbox-toggle').on('click', function() {
                const checked = $(this).find('i').hasClass('bx-square');
                $(this).find('i').toggleClass('bx-square bx-check-square');
                $('#mailbox-messages input[type="checkbox"]').prop('checked', checked);
            });
            
            // Delete selected
            $('#delete-selected').on('click', function() {
                const selected = $('#mailbox-messages input[type="checkbox"]:checked').map(function() {
                    return $(this).data('id');
                }).get();
                
                if (selected.length > 0) {
                    // Show the confirmation modal
                    $('#deleteConfirmationModal').data('ids', selected).fadeIn();
                }
            });
            
            // Handle the confirmation modal actions
            $('#confirm-delete').on('click', function() {
                const ids = $('#deleteConfirmationModal').data('ids');
                if (ids && ids.length > 0) {
                    $.ajax({
                        url: '../../Contactus/api/messagesApi.php',
                        type: 'POST',
                        data: {
                            action: 'delete_messages',
                            ids: ids.join(',')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Refresh messages
                                fetchMessages();
                                // Show success message (could be enhanced with a toast/notification)
                                alert(response.message);
                            } else {
                                alert(response.message || 'Error deleting messages');
                            }
                            // Hide modal
                            $('#deleteConfirmationModal').fadeOut();
                        },
                        error: function() {
                            alert('Error connecting to server');
                            $('#deleteConfirmationModal').fadeOut();
                        }
                    });
                }
            });
            
            $('#confirm-trash').on('click', function() {
                const ids = $('#deleteConfirmationModal').data('ids');
                if (ids && ids.length > 0) {
                    $.ajax({
                        url: '../../Contactus/api/messagesApi.php',
                        type: 'POST',
                        data: {
                            action: 'move_to_trash',
                            ids: ids.join(',')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Refresh messages
                                fetchMessages();
                                // Show success message
                                alert(response.message);
                            } else {
                                alert(response.message || 'Error moving messages to trash');
                            }
                            // Hide modal
                            $('#deleteConfirmationModal').fadeOut();
                        },
                        error: function() {
                            alert('Error connecting to server');
                            $('#deleteConfirmationModal').fadeOut();
                        }
                    });
                }
            });
            
            // Close modal when Cancel or X is clicked
            $('.mailbox-close, [data-dismiss="modal"]').on('click', function() {
                $('#deleteConfirmationModal').fadeOut();
            });
            
            // Refresh mailbox
            $('#refresh-mail').on('click', function() {
                fetchMessages(currentStatus, currentPage, currentSearch);
            });
            
            // Pagination
            $('#prev-page, #prev-page2').on('click', function() {
                if (currentPage > 1) {
                    fetchMessages(currentStatus, currentPage - 1, currentSearch);
                }
            });
            
            $('#next-page, #next-page2').on('click', function() {
                fetchMessages(currentStatus, currentPage + 1, currentSearch);
            });
            
            // Star message (delegated event because rows are dynamically created)
            $(document).on('click', '.star-message', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const icon = $(this).find('i');
                
                $.ajax({
                    url: '../../Contactus/api/messagesApi.php',
                    type: 'POST',
                    data: {
                        action: 'toggle_star',
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Toggle star icon class
                            if (response.is_starred) {
                                icon.removeClass('fa-star-o mailbox-star-o').addClass('fa-star mailbox-star');
                            } else {
                                icon.removeClass('fa-star mailbox-star').addClass('fa-star-o mailbox-star-o');
                            }
                            
                            // If in the starred folder, refresh to remove unstarred items
                            if (currentStatus === 'starred' && !response.is_starred) {
                                fetchMessages(currentStatus, currentPage, currentSearch);
                            }
                        }
                    }
                });
            });
            
            // Message click - view message (delegated event)
            $(document).on('click', 'td.mailbox-name, td.mailbox-subject', function() {
                const messageId = $(this).closest('tr').find('input[type="checkbox"]').data('id');
                
                if (messageId) {
                    // Load message details (you would need to create a view message modal or page)
                    $.ajax({
                        url: '../../Contactus/api/messagesApi.php',
                        type: 'GET',
                        data: {
                            action: 'get_message',
                            id: messageId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Display message in a modal (you'll need to create this modal)
                                displayMessageModal(response.message);
                                
                                // Refresh message list to update read status if needed
                                fetchMessages(currentStatus, currentPage, currentSearch);
                            } else {
                                alert(response.message || 'Error loading message');
                            }
                        },
                        error: function() {
                            alert('Error connecting to server');
                        }
                    });
                }
            });
            
            // Function to display message in a modal
            function displayMessageModal(message) {
                // Check if modal already exists, if not create it
                if ($('#viewMessageModal').length === 0) {
                    $('body').append(`
                        <div id="viewMessageModal" class="mailbox-modal">
                            <div class="mailbox-modal-dialog" style="max-width: 700px;">
                                <div class="mailbox-modal-content">
                                    <div class="mailbox-modal-header">
                                        <h5 class="mailbox-modal-title" id="messageSubject"></h5>
                                        <button type="button" class="mailbox-close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="mailbox-modal-body">
                                        <div class="mailbox-message-info">
                                            <p><strong>From:</strong> <span id="messageSender"></span></p>
                                            <p><strong>Email:</strong> <span id="messageEmail"></span></p>
                                            <p><strong>Phone:</strong> <span id="messagePhone"></span></p>
                                            <p><strong>Date:</strong> <span id="messageDate"></span></p>
                                        </div>
                                        <hr>
                                        <div id="messageContent" class="mailbox-message-content"></div>
                                    </div>
                                    <div class="mailbox-modal-footer">
                                        <button type="button" class="mailbox-btn mailbox-btn-default" data-dismiss="modal">Close</button>
                                        <button type="button" class="mailbox-btn mailbox-bg-maroon" id="modal-delete-message"><i class="fa fa-trash"></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    // Add event handlers for the modal
                    $(document).on('click', '[data-dismiss="modal"]', function() {
                        $('#viewMessageModal').fadeOut();
                    });
                    
                    $(document).on('click', '#modal-delete-message', function() {
                        const messageId = $('#viewMessageModal').data('message-id');
                        
                        if (messageId) {
                            $.ajax({
                                url: '../../Contactus/api/messagesApi.php',
                                type: 'POST',
                                data: {
                                    action: 'move_to_trash',
                                    ids: messageId
                                },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        $('#viewMessageModal').fadeOut();
                                        fetchMessages(currentStatus, currentPage, currentSearch);
                                        alert(response.message);
                                    } else {
                                        alert(response.message || 'Error moving message to trash');
                                    }
                                }
                            });
                        }
                    });
                    
                    // Close modal when clicking outside of it
                    $(window).on('click', function(event) {
                        if ($(event.target).is('#viewMessageModal')) {
                            $('#viewMessageModal').fadeOut();
                        }
                    });
                }
                
                // Populate modal with message data
                $('#messageSubject').text(message.subject);
                $('#messageSender').text(message.names);
                $('#messageEmail').text(message.email);
                $('#messagePhone').text(message.phone || 'N/A');
                $('#messageDate').text(message.formatted_date);
                $('#messageContent').text(message.message);
                
                // Store message ID in the modal
                $('#viewMessageModal').data('message-id', message.id);
                
                // Show the modal
                $('#viewMessageModal').fadeIn();
            }
            
            // When clicking outside the delete confirmation modal, close it
            $(window).on('click', function(event) {
                if ($(event.target).is('#deleteConfirmationModal')) {
                    $('#deleteConfirmationModal').fadeOut();
                }
            });
        });
    </script>

</body>

</html>