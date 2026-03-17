<?php
/**
 * MM SPG Admin Menu - API Documentation
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin submenu for API Documentation under SPG Steps
 */
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=spg_step',
        'API Documentation',
        'API Docs',
        'manage_options',
        'mm-spg-api-docs',
        'mm_spg_render_api_docs_page'
    );
});

/**
 * Render API Documentation Page
 */
function mm_spg_render_api_docs_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    ?>
    <div class="wrap">
        <h1>MM SPG - API Documentation</h1>
        
        <!-- Tab Navigation -->
        <div style="margin: 20px 0;">
            <div class="nav-tab-wrapper" style="background: #f5f5f5; border-bottom: 1px solid #ddd; padding: 0; display: flex;">
                <a href="#auth-docs" class="nav-tab nav-tab-active" onclick="mm_spg_switch_tab(event, 'auth-docs')" style="padding: 10px 20px; cursor: pointer; background: white; border: 1px solid #ddd; border-bottom: none; margin-right: 5px;">🔐 Authentication</a>
                <a href="#interests-docs" class="nav-tab" onclick="mm_spg_switch_tab(event, 'interests-docs')" style="padding: 10px 20px; cursor: pointer; background: #f5f5f5; border: 1px solid #ddd; border-bottom: none; margin-right: 5px;">📚 Interests</a>
                <a href="#business-card-docs" class="nav-tab" onclick="mm_spg_switch_tab(event, 'business-card-docs')" style="padding: 10px 20px; cursor: pointer; background: #f5f5f5; border: 1px solid #ddd; border-bottom: none; margin-right: 5px;">💼 Business Card</a>
                <a href="#social-links-docs" class="nav-tab" onclick="mm_spg_switch_tab(event, 'social-links-docs')" style="padding: 10px 20px; cursor: pointer; background: #f5f5f5; border: 1px solid #ddd; border-bottom: none;">🔗 Social Links</a>
            </div>
        </div>

        <!-- Authentication Documentation Tab -->
        <div id="auth-docs" class="mm-spg-tab-content" style="display: block;">
            <div style="max-width: 900px; margin: 20px 0;">
                <!-- POST Login -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">1. POST Login</h3>
                    <p><strong>Endpoint:</strong> <code>POST /wp-json/api/v1/auth/login</code></p>
                    
                    <p><strong>Description:</strong> Authenticate user with username and password</p>
                    
                    <p><strong>Auth:</strong> Not required (Public endpoint)</p>
                    
                    <p><strong>Request Body:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "username": "testuser",
  "password": "password123"
}</pre>

                    <p><strong>Success Response (200):</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user_id": 1,
    "username": "testuser",
    "email": "test@example.com",
    "first_name": "Test",
    "last_name": "User",
    "roles": ["subscriber"],
    "nonce": "abc123def456xyz789..."
  }
}</pre>

                    <p><strong style="color: #d32f2f;">💡 Important:</strong> Save the <code>nonce</code> value. Use it in all subsequent API requests by adding <code>?nonce=abc123def456xyz789...</code> to the URL or in the request body.</p>

                    <p><strong>Error Response (401):</strong></p>
                    <pre style="background: #ffebee; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": false,
  "message": "Invalid username or password",
  "code": "invalid_credentials"
}</pre>

                    <p><strong>JavaScript Example:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
async function login(username, password) {
  const response = await fetch('/wp-json/api/v1/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    credentials: 'include',
    body: JSON.stringify({
      username: username,
      password: password
    })
  });
  
  const result = await response.json();
  console.log(result);
}

login('testuser', 'password123');</pre>
                </div>

            <!-- GET Current User -->
            <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                <h3 style="color: #0073aa; margin-top: 0;">2. GET Current User</h3>
                <p><strong>Endpoint:</strong> <code>GET /wp-json/api/v1/auth/me</code></p>
                
                <p><strong>Description:</strong> Get current logged-in user information</p>
                
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "data": {
    "user_id": 1,
    "username": "testuser",
    "email": "test@example.com",
    "first_name": "Test",
    "last_name": "User",
    "roles": ["subscriber"]
  }
}</pre>

                <p><strong>JavaScript Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
async function getCurrentUser() {
  const response = await fetch('/wp-json/api/v1/auth/me', {
    credentials: 'include'
  });
  
  const result = await response.json();
  console.log(result);
}

getCurrentUser();</pre>
            </div>

            <!-- POST Logout -->
            <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                <h3 style="color: #0073aa; margin-top: 0;">3. POST Logout</h3>
                <p><strong>Endpoint:</strong> <code>POST /wp-json/api/v1/auth/logout</code></p>
                
                <p><strong>Description:</strong> Logout current user and clear session</p>
                
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "message": "Logged out successfully"
}</pre>

                <p><strong>JavaScript Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
async function logout() {
  const response = await fetch('/wp-json/api/v1/auth/logout', {
    method: 'POST',
    credentials: 'include'
  });
  
  const result = await response.json();
  console.log(result);
}

logout();</pre>
                </div>
            </div>
        </div>

        <!-- Interests Documentation Tab -->
        <div id="interests-docs" class="mm-spg-tab-content" style="display: none;">
            <div style="max-width: 900px; margin: 20px 0;">
            <!-- GET All Interests -->
            <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                <h3 style="color: #0073aa; margin-top: 0;">4. GET All Available Interests</h3>
                <p><strong>Endpoint:</strong> <code>GET /wp-json/api/v1/spg/interests</code></p>
                
                <p><strong>Description:</strong> Fetch all available interests/categories in the system</p>
                
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;"
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Technology",
      "slug": "technology"
    },
    {
      "id": 2,
      "name": "Marketing",
      "slug": "marketing"
    }
  ],
  "count": 2
}</pre>

                <p><strong>JavaScript Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
fetch('/wp-json/api/v1/spg/interests')
  .then(res => res.json())
  .then(data => console.log(data));</pre>
            </div>

            <!-- POST Save Interests -->
            <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                <h3 style="color: #0073aa; margin-top: 0;">5. POST Save User Interests with Priorities</h3>
                <p><strong>Endpoint:</strong> <code>POST /wp-json/api/v1/spg/interests/save</code></p>
                
                <p><strong>Description:</strong> Save user's selected interests with priority levels (1-5)</p>
                
                <p><strong>Auth:</strong> Required (Nonce required)</p>
                
                <p><strong>Request Body Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "nonce": "abc123def456xyz789...",
  "interests": [
    {
      "id": 1,
      "priority": 1
    },
    {
      "id": 2,
      "priority": 2
    },
    {
      "id": 5,
      "priority": 3
    }
  ]
}</pre>

                <p><strong>Success Response:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "message": "Interests saved successfully",
  "data": {
    "interest_ids": [1, 2, 5],
    "priorities": {
      "1": 1,
      "2": 2,
      "5": 3
    },
    "saved_at": "2026-03-10 10:30:45",
    "phase_2_completed": true
  }
}</pre>

                <p><strong>Error Responses:</strong></p>
                <pre style="background: #ffebee; padding: 12px; border-radius: 4px; overflow-x: auto;">
// Empty interests
{
  "success": false,
  "message": "At least one interest must be selected",
  "code": "empty_interests"
}

// No first priority
{
  "success": false,
  "message": "At least one interest must be assigned 1st priority",
  "code": "no_first_priority"
}

// Duplicate priorities
{
  "success": false,
  "message": "Duplicate priorities not allowed",
  "code": "duplicate_priorities"
}

// Invalid priority
{
  "success": false,
  "message": "Invalid priority. Must be 1-5",
  "code": "invalid_priority"
}</pre>

                <p><strong>JavaScript Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
async function saveInterests() {
  const response = await fetch('/wp-json/api/v1/spg/interests/save', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      interests: [
        { id: 1, priority: 1 },
        { id: 2, priority: 2 },
        { id: 5, priority: 3 }
      ]
    })
  });
  
  const result = await response.json();
  console.log(result);
}

saveInterests();</pre>
            </div>

            <!-- GET User Interests -->
            <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                <h3 style="color: #0073aa; margin-top: 0;">6. GET User's Saved Interests</h3>
                <p><strong>Endpoint:</strong> <code>GET /wp-json/api/v1/spg/interests/user</code></p>
                
                <p><strong>Description:</strong> Fetch current user's saved interests with priorities (sorted by priority)</p>
                
                <p><strong>Auth:</strong> Required (Nonce required)</p>

                <p><strong>Request Parameters:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...
                
                <p><strong>Response Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "data": {
    "interests": [
      {
        "id": 1,
        "name": "Technology",
        "slug": "technology",
        "priority": 1
      },
      {
        "id": 2,
        "name": "Marketing",
        "slug": "marketing",
        "priority": 2
      },
      {
        "id": 5,
        "name": "Design",
        "slug": "design",
        "priority": 3
      }
    ],
    "completed": true,
    "saved_count": 3
  }
}</pre>

                <p><strong>JavaScript Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
async function getUserInterests() {
  const response = await fetch('/wp-json/api/v1/spg/interests/user');
  const result = await response.json();
  
  console.log('User interests:', result.data.interests);
  console.log('Is completed:', result.data.completed);
}

getUserInterests();</pre>
            </div>

            <!-- DELETE Clear Interests -->
            <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                <h3 style="color: #0073aa; margin-top: 0;">7. DELETE Clear User Interests</h3>
                <p><strong>Endpoint:</strong> <code>DELETE /wp-json/api/v1/spg/interests/clear</code></p>
                
                <p><strong>Description:</strong> Clear all saved interests for current user</p>
                
                <p><strong>Auth:</strong> Required (Nonce required)</p>

                <p><strong>Request Parameters:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...
                
                <p><strong>Response Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "message": "Interests cleared successfully"
}</pre>

                <p><strong>JavaScript Example:</strong></p>
                <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
async function clearInterests() {
  const response = await fetch('/wp-json/api/v1/spg/interests/clear', {
    method: 'DELETE'
  });
  
  const result = await response.json();
  console.log(result);
}

clearInterests();</pre>
            </div>

            <!-- Validation Rules -->
            <div style="background: #fff3e0; padding: 20px; margin: 15px 0; border-left: 4px solid #ff9800; border-radius: 4px;">
                <h3 style="color: #e65100; margin-top: 0;">Validation Rules</h3>
                <ul>
                    <li><strong>Minimum Interests:</strong> At least 1 interest must be selected</li>
                    <li><strong>First Priority:</strong> At least 1 interest must be assigned priority 1</li>
                    <li><strong>Priority Range:</strong> Priority must be 1-5</li>
                    <li><strong>Unique Priorities:</strong> No duplicate priority values allowed</li>
                </ul>
            </div>

            <!-- Priority Levels -->
            <div style="background: #e8f5e9; padding: 20px; margin: 15px 0; border-left: 4px solid #4caf50; border-radius: 4px;">
                <h3 style="color: #2e7d32; margin-top: 0;">Priority Levels</h3>
                <ul>
                    <li><strong>1:</strong> Highest Priority (First)</li>
                    <li><strong>2:</strong> Second Priority</li>
                    <li><strong>3:</strong> Third Priority</li>
                    <li><strong>4:</strong> Fourth Priority</li>
                    <li><strong>5:</strong> Lowest Priority (Fifth)</li>
                </ul>
            </div>

            <!-- User Meta Keys -->
            <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                <h3 style="color: #0073aa; margin-top: 0;">User Meta Keys (Storage)</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="background: #f5f5f5;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Meta Key</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Type</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Example</th>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>user_categories</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Array</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>[1, 2, 5]</code></td>
                    </tr>
                    <tr style="background: #f9f9f9;">
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>user_categories_priority</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Array</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>{"1": 1, "2": 2, "5": 3}</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>mm_spg_interest_completed</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Integer</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>1</code></td>
                    </tr>
                </table>
            </div>

        </div>
        </div>

        <!-- Business Card Documentation Tab -->
        <div id="business-card-docs" class="mm-spg-tab-content" style="display: none;">
            <div style="max-width: 900px; margin: 20px 0;">
                <!-- GET Business Card Fields -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">8. GET Business Card Fields Template</h3>
                    <p><strong>Endpoint:</strong> <code>GET /wp-json/api/v1/spg/business-card/fields</code></p>
                    
                    <p><strong>Description:</strong> Fetch available business card fields and form template</p>
                    
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...</pre>

                    <p><strong>Response Example (partial):</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "data": [
    {
      "name": "full_name",
      "label": "Full Name",
      "type": "text",
      "required": true,
      "placeholder": "Your full name"
    },
    {
      "name": "job_title",
      "label": "Job Title",
      "type": "text",
      "required": true
    },
    {
      "name": "keywords",
      "label": "Keywords/Expertise",
      "type": "textarea",
      "required": true,
      "hint": "e.g., Web Development, UI Design, Marketing Strategy"
    }
  ],
  "count": 7
}</pre>
                </div>

                <!-- POST Save Business Card -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">9. POST Save Business Card Details</h3>
                    <p><strong>Endpoint:</strong> <code>POST /wp-json/api/v1/spg/business-card/save</code></p>
                    
                    <p><strong>Description:</strong> Save user's business card information (name, title, keywords, social links)</p>
                    
                    <p><strong>Auth:</strong> Required (Nonce required)</p>
                    
                    <p><strong>Request Body Example:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "nonce": "abc123def456xyz789...",
  "full_name": "Joshua Samrat Joseph",
  "job_title": "Full Stack Developer",
  "company_name": "Tech Startup Inc",
  "keywords": "PHP, React, REST APIs, Web Development, UI Design",
  "phone": "+1 (555) 123-4567",
  "email": "joshua@example.com",
  "website": "https://joshuasamrat.com",
  "social_links": {
    "linkedin": "https://linkedin.com/in/joshuasamrat",
    "twitter": "https://twitter.com/joshuasamrat",
    "github": "https://github.com/joshuasamrat",
    "instagram": "https://instagram.com/joshuasamrat"
  }
}</pre>

                    <p><strong>Success Response (200):</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "message": "Business card saved successfully",
  "data": {
    "business_card": {
      "full_name": "Joshua Samrat Joseph",
      "job_title": "Full Stack Developer",
      "company_name": "Tech Startup Inc",
      "keywords": "PHP, React, REST APIs, Web Development, UI Design",
      "phone": "+1 (555) 123-4567",
      "email": "joshua@example.com",
      "website": "https://joshuasamrat.com"
    },
    "saved_at": "2026-03-10 10:45:30",
    "phase_3_started": true
  }
}</pre>

                    <p><strong>Error Responses:</strong></p>
                    <pre style="background: #ffebee; padding: 12px; border-radius: 4px; overflow-x: auto;">
// Missing full name
{
  "success": false,
  "message": "Full name is required",
  "code": "missing_full_name"
}

// Missing keywords
{
  "success": false,
  "message": "Keywords/expertise is required",
  "code": "missing_keywords"
}

// Invalid email
{
  "success": false,
  "message": "Valid email address is required",
  "code": "invalid_email"
}
}</pre>
                </div>

                <!-- GET User Business Card -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">10. GET User's Saved Business Card</h3>
                    <p><strong>Endpoint:</strong> <code>GET /wp-json/api/v1/spg/business-card/user</code></p>
                    
                    <p><strong>Description:</strong> Fetch current user's saved business card details</p>
                    
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...</pre>

                    <p><strong>Response Example:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "data": {
    "business_card": {
      "full_name": "Joshua Samrat Joseph",
      "job_title": "Full Stack Developer",
      "company_name": "Tech Startup Inc",
      "keywords": "PHP, React, REST APIs, Web Development",
      "email": "joshua@example.com",
      "social_links": {
        "linkedin": "https://linkedin.com/in/joshuasamrat",
        "github": "https://github.com/joshuasamrat"
      }
    },
    "completed": true,
    "saved_at": "2026-03-10 10:45:30"
  }
}</pre>
                </div>

                <!-- DELETE Clear Business Card -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">11. DELETE Clear Business Card</h3>
                    <p><strong>Endpoint:</strong> <code>DELETE /wp-json/api/v1/spg/business-card/clear</code></p>
                    
                    <p><strong>Description:</strong> Clear all saved business card information for current user</p>
                    
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...</pre>

                    <p><strong>Response Example:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "message": "Business card cleared successfully"
}</pre>
                </div>

            </div>
        </div>

        <!-- Social Links Documentation Tab -->
        <div id="social-links-docs" class="mm-spg-tab-content" style="display: none;">
            <div style="max-width: 900px; margin: 20px 0;">
                <!-- GET Social Platforms -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">12. GET Available Social Platforms</h3>
                    <p><strong>Endpoint:</strong> <code>GET /wp-json/api/v1/spg/social-links/platforms</code></p>
                    
                    <p><strong>Description:</strong> Fetch list of available social media platforms</p>
                    
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...</pre>

                    <p><strong>Response Example (partial):</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "data": [
    {
      "platform": "linkedin",
      "label": "LinkedIn",
      "icon": "fab fa-linkedin",
      "base_url": "https://linkedin.com/in/",
      "placeholder": "https://linkedin.com/in/yourprofile"
    },
    {
      "platform": "twitter",
      "label": "Twitter",
      "icon": "fab fa-twitter",
      "placeholder": "https://twitter.com/yourhandle"
    },
    {
      "platform": "github",
      "label": "GitHub",
      "icon": "fab fa-github",
      "placeholder": "https://github.com/yourprofile"
    },
    {
      "platform": "instagram",
      "label": "Instagram",
      "icon": "fab fa-instagram",
      "placeholder": "https://instagram.com/yourhandle"
    },
    {
      "platform": "youtube",
      "label": "YouTube",
      "icon": "fab fa-youtube",
      "placeholder": "https://youtube.com/@yourchannel"
    }
  ],
  "count": 8
}</pre>
                </div>

                <!-- POST Save Social Links -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">13. POST Save Social Links</h3>
                    <p><strong>Endpoint:</strong> <code>POST /wp-json/api/v1/spg/social-links/save</code></p>
                    
                    <p><strong>Description:</strong> Save user's social media profile links (minimum 1 required)</p>
                    
                    <p><strong>Auth:</strong> Required (Nonce required)</p>
                    
                    <p><strong>Request Body Example:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "nonce": "abc123def456xyz789...",
  "social_links": {
    "linkedin": "https://linkedin.com/in/joshua",
    "twitter": "https://twitter.com/joshua",
    "github": "https://github.com/joshua",
    "instagram": "https://instagram.com/joshua",
    "youtube": "https://youtube.com/@joshua"
  }
}</pre>

                    <p><strong>Success Response (200):</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "message": "Social links saved successfully",
  "data": {
    "social_links": {
      "linkedin": "https://linkedin.com/in/joshua",
      "twitter": "https://twitter.com/joshua",
      "github": "https://github.com/joshua",
      "instagram": "https://instagram.com/joshua",
      "youtube": "https://youtube.com/@joshua"
    },
    "saved_at": "2026-03-11 08:15:20",
    "count": 5
  }
}</pre>

                    <p><strong>Error Responses:</strong></p>
                    <pre style="background: #ffebee; padding: 12px; border-radius: 4px; overflow-x: auto;">
// No social links provided
{
  "success": false,
  "message": "At least one social link must be provided",
  "code": "empty_social_links"
}

// No valid social links
{
  "success": false,
  "message": "At least one valid social link must be provided",
  "code": "no_valid_social_links"
}</pre>
                </div>

                <!-- GET User Social Links -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">14. GET User's Social Links</h3>
                    <p><strong>Endpoint:</strong> <code>GET /wp-json/api/v1/spg/social-links/user</code></p>
                    
                    <p><strong>Description:</strong> Fetch current user's saved social media links with platform details</p>
                    
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...</pre>

                    <p><strong>Response Example:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "data": {
    "social_links": [
      {
        "platform": "linkedin",
        "label": "LinkedIn",
        "url": "https://linkedin.com/in/joshua",
        "icon": "fab fa-linkedin"
      },
      {
        "platform": "github",
        "label": "GitHub",
        "url": "https://github.com/joshua",
        "icon": "fab fa-github"
      },
      {
        "platform": "twitter",
        "label": "Twitter",
        "url": "https://twitter.com/joshua",
        "icon": "fab fa-twitter"
      }
    ],
    "completed": true,
    "count": 3
  }
}</pre>
                </div>

                <!-- DELETE Clear Social Links -->
                <div style="background: white; padding: 20px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="color: #0073aa; margin-top: 0;">15. DELETE Clear Social Links</h3>
                    <p><strong>Endpoint:</strong> <code>DELETE /wp-json/api/v1/spg/social-links/clear</code></p>
                    
                    <p><strong>Description:</strong> Clear all saved social media links for current user</p>
                    
                    <p><strong>Auth:</strong> Required (Nonce required)</p>

                    <p><strong>Request Parameters:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
?nonce=abc123def456xyz789...</pre>

                    <p><strong>Response Example:</strong></p>
                    <pre style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto;">
{
  "success": true,
  "message": "Social links cleared successfully"
}</pre>
                </div>

            </div>
        </div>

    </div>

    <script>
        function mm_spg_switch_tab(event, tabName) {
            event.preventDefault();
            
            // Hide all tabs
            const tabs = document.querySelectorAll('.mm-spg-tab-content');
            tabs.forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remove active class from all nav tabs
            const navTabs = document.querySelectorAll('.nav-tab');
            navTabs.forEach(tab => {
                tab.classList.remove('nav-tab-active');
            });
            
            // Show selected tab
            document.getElementById(tabName).style.display = 'block';
            
            // Add active class to clicked tab
            event.target.classList.add('nav-tab-active');
        }
    </script>

    <?php
}

/**
 * Add admin styles
 */
add_action('admin_head', function () {
    ?>
    <style>
        #adminmenu li.toplevel_page_mm-spg-api-docs > a {
            font-weight: bold;
        }
        
        .wrap code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        
        .wrap pre {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 4px;
            overflow-x: auto;
            border: 1px solid #ddd;
        }

        .nav-tab-wrapper {
            display: flex;
            background: #f5f5f5;
            border-bottom: 2px solid #ddd;
            padding: 0;
        }

        .nav-tab {
            padding: 10px 20px;
            cursor: pointer;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-bottom: none;
            margin-right: 5px;
            text-decoration: none;
            color: #23282d;
            transition: all 0.3s ease;
        }

        .nav-tab:hover {
            background: #efefef;
        }

        .nav-tab-active {
            background: white !important;
            border-bottom: 3px solid #0073aa !important;
            color: #0073aa;
            font-weight: 600;
        }

        .mm-spg-tab-content {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
    <?php
});
