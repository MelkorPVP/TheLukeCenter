<?php
    $container = require_once __DIR__ . '/app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];
    
    $pageTitle = 'Catalytic Leadership – The Luke Center';
    $activeNav = 'catalytic';
    
    // Insert HTML header.
    require app_public_path('header.php', APP_ENVIRONMENT);
?>
<section class="hero text-center text-hero border-bottom">
    <div class="container py-4">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Catalytic Leadership</h1>
        <p class="fs-5 mb-0">Navigating our interconnected world through strategy.</p>
    </div>
</section>
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <p>In the late 1980's, Dr. Jeff Luke of the University of Oregon undertook a research project to find out why some communities were more successful dealing with difficult issues and solving complex problems.</p>
                <p>His answer came after he studied community challenges in which multiple groups came together, ones in which no one group had clear ownership over the problem or the process.</p>
                <p>Jeff found the primary factor for success was a certain type of leadership, which he called Catalytic Leadership. Catalytic Leaders engage and motivate others to take on leadership roles and work toward a shared vision.</p>
                <p>Dr. Luke decided that the skills of Catalytic Leadership are teachable. In 1989, he brought together Catalytic Leaders from around the country to train Pacific Northwest leadership. Thus The Pacific Program was born.</p>
                <h2 class="h4 mt-4">The Catalytic Leadership Skill Set</h2>
                <ul>
                    <li><strong>Raising Awareness:</strong> Effective leadership involves focusing public attention on the issue.</li>
                    <li><strong>Forming Work Groups:</strong> Bringing people together to address the problem is essential for lasting solutions.</li>
                    <li><strong>Creating Strategies:</strong> We aim to stimulate multiple strategies and options for action.</li>
                    <li><strong>Sustaining Action:</strong> Strong implementation strategies keeps momentum alive.</li>
                    <li><strong>Thinking and Acting Strategically:</strong> We frame challenges in ways that reveal leverage points and pathways for impact.</li>
                    <li><strong>Facilitating Productive Work Groups:</strong> Productive work groups thrive when conflict is managed and progress is sustained.</li>
                    <li><strong>Leading from Personal Passion and Strength of Character:</strong> Character and personal commitment inspire trust and drive results.</li>
                </ul>
                <div class="col-12 col-md-6 mx-auto">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body p-3 p-md-4">
                            <button
                            id="clOverviewToggle"
                            class="btn btn-outline-brand w-100 text-start lc-expand-toggle collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#clOverviewCollapse"
                            aria-expanded="false"
                            aria-controls="clOverviewCollapse"
                            data-cl-overview-toggle
                            >
                                <span class="d-flex w-100 align-items-center justify-content-between gap-3">
                                    <span>
                                        <span class="d-block fw-bold" data-cl-toggle-title>Catalytic Leadership Overview</span>
                                        <span class="d-block small lc-toggle-subtitle" data-cl-toggle-subtitle>Click to expand and learn more</span>
                                    </span>
                                    <i class="bi bi-chevron-down lc-collapse-chevron" aria-hidden="true"></i>
                                </span>
                            </button>
                            
                            <div class="collapse mt-3" id="clOverviewCollapse">
                                <div class="section-accent rounded-4 border p-3 p-md-4 lc-overview-content">
                                    <div id="clTop">
                                        <h3 class="h5 text-brand mb-0">Catalytic Leadership</h3>
                                        <p class="text-secondary mb-3">By Jeffery S. Luke</p>
                                        
                                        <div class="mb-3">
                                            <div class="small text-secondary mb-2">Jump to a chapter:</div>
                                            <div class="row row-cols-1 row-cols-md-2 g-2 lc-toc">
                                                <div class="col">
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch1">Chapter One: Interconnected Public Problems</a>
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch2">Chapter Two: Defining Public Leadership</a>
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch3">Chapter Three: Raising Awareness</a>
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch4">Chapter Four: Forming Work Groups</a>
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch5">Chapter Five: Creating Strategies</a>
                                                </div>
                                                <div class="col">
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch6">Chapter Six: Sustaining Action</a>
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch7">Chapter Seven: Thinking Strategically</a>
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch8">Chapter Eight: Facilitating Work Groups</a>
                                                    <a class="text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#cl-ch9">Chapter Nine: Passion &amp; Character</a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p>
                                            This book was written 1998 to address the shrinking of the world, as we know it.
                                            Our world is a truly interconnected world. The question is posed:
                                        </p>
                                        
                                        <p class="mb-2">
                                            How do we provide effective leadership in Public Sector organizations to address the interconnected problems with:
                                        </p>
                                        
                                        <ul>
                                            <li>Reduced fiscal resources?</li>
                                            <li>A lack of consensus on options?</li>
                                            <li>The involvement of diverse, independent minded stakeholders?</li>
                                        </ul>
                                        
                                        <p>
                                            Successful action required commitment by numerous public and private sector agencies.
                                            To create strategic action on urgent public problems, federal, state, local entities and communities have to reach out beyond their boundaries and engage a wider set of individual, agencies and stakeholders.
                                            Tackling public problems required unusual and dynamic partnerships between government, non profit, businesses, neighborhoods, tribal nations and educational institutions.
                                        </p>
                                        
                                        <p>
                                            Public leadership is an activity engaged in by citizens of all walks of life: elected and appointed, public and private, paid and volunteer, urban and rural.
                                        </p>
                                        
                                        <p class="mb-0">
                                            Public problems are interconnected, they cross organizational and jurisdictional boundaries; they are interorganizational.
                                            No single agency, organization, jurisdiction or sector has enough authority, influence or resources to dictate visionary solutions.
                                        </p>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch1">
                                        <h4 class="h6 text-brand mb-2">Chapter One: “The Interconnected Nature of Public Problems”</h4>
                                        <p class="mb-0">
                                            A public problem is a discrepancy or gap between a current situation or condition and a desired condition or situation.
                                            How a problem is defined has a very powerful influence on the strategies, actions and interventions that seem appropriate to narrow the gap of what is and what to be.
                                        </p>
                                    </section>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch2">
                                        <h4 class="h6 text-brand mb-2">Chapter Two: “Defining Public Leadership”</h4>
                                        <p class="mb-2">Effective public leadership requires the catalytic leader to:</p>
                                        <ul class="mb-3">
                                            <li>Focus attention by elevating the issue to the public and policy agenda.</li>
                                            <li>Engage people in the effort by convening a diverse set of people, agencies, and interests needed to address the issue.</li>
                                            <li>Stimulate multiple strategies and options for action.</li>
                                            <li>Maintain action and momentum by managing the interconnections with rapid information sharing and feedback.</li>
                                        </ul>
                                        
                                        <div class="rounded-4 bg-white border p-3">
                                            <p class="mb-1 fw-semibold text-secondary">Catalytic Leadership Overview (2012)</p>
                                            <p class="mb-0">
                                                Today’s public leaders must be catalytic, thinking about problems and solutions in a systematic or interconnected way.
                                                One must elevate the issue to the public agenda, convene critical stakeholders, stimulate multiple initiatives to achieve goals and sustain action over the long term.
                                            </p>
                                        </div>
                                    </section>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch3">
                                        <h4 class="h6 text-brand mb-2">Chapter Three: “Raising Awareness: Focusing Public Attention on the Issue”</h4>
                                        <p>
                                            Public leaders need to direct attention towards an issue because the list of potential issues is vast and resources limited.
                                            Leaders must advocate ensuring that an issue is seen as more important than competing topics.
                                            The message has to be that this issue is urgent or important enough to invest time and energy in.
                                        </p>
                                        
                                        <h5 class="h6 text-brand mb-2">Catalytic Strategies</h5>
                                        <ol>
                                            <li>Intellectual awareness of a worsening condition or troubling comparison.</li>
                                            <li>Emotional concern regarding the condition.</li>
                                            <li>Sense the problem is urgent.</li>
                                            <li>Believe that the problem can be addressed (give hope)</li>
                                        </ol>
                                        
                                        <p class="mb-0">
                                            The framing of a problem is an important catalytic task. A single problem can be defined various ways by diverse stakeholders.
                                        </p>
                                    </section>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch4">
                                        <h4 class="h6 text-brand mb-2">Chapter Four: “Forming Work Groups”</h4>
                                        <p>
                                            Stakeholders are defined as individuals, groups or organizations with interest in an area or issue.
                                        </p>
                                        
                                        <p class="mb-2">Bringing stakeholders together can occur in two ways:</p>
                                        <ol>
                                            <li>Convene around a particular issue/ problem</li>
                                            <li>Convene around a preferred solution to a problem or issue.</li>
                                        </ol>
                                        
                                        <p>
                                            Successful working groups must have a unique purpose (vision) and they must trust each other.
                                            Forming relationships between the stakeholders and members of the group is paramount.
                                        </p>
                                        
                                        <p>
                                            In the first meeting ground rules, norms and expectations should be defined.
                                            Defining and creating procedural ground rules that are written, help to create a fair, safe and legitimate process.
                                        </p>
                                        
                                        <p class="mb-0">
                                            Acting as catalysts, public leaders use their knowledge, knowledge of stakeholders’ interests, their personal contacts in related networks, personal charm and available authority to convince key individuals that the issue is worthy of their involvement.
                                        </p>
                                    </section>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch5">
                                        <h4 class="h6 text-brand mb-2">Chapter Five: Creating Strategies</h4>
                                        <p>
                                            Effective work groups develop a clear understanding of their purpose and objectives.
                                            Trust is a foundation for cooperative, collaborative and collective efforts.
                                            Trust within a group ensures members stay focused on the outcome.
                                        </p>
                                        
                                        <p>
                                            Effective work groups postpone solution generation and first spend time defining the problem, translating it from an issue to desired outcome.
                                            Then they consider multiple strategies to achieve the outcome.
                                        </p>
                                        
                                        <p>
                                            Scientific or technical evidence applied to public problems help define the issue and set a foundation of facts for the stakeholders creating a mental model or shared frame work.
                                        </p>
                                        
                                        <p>
                                            Interconnected problems require multiple strategies rather than one comprehensive solution to achieve sustained improvement.
                                        </p>
                                        
                                        <p class="mb-0">
                                            Self organizing efforts allow agencies groups and individuals to participate in ways they know best and about most.
                                            Less time and resources are lost on fighting about who is in charge and conflict on which strategy to commit to is eliminated.
                                        </p>
                                    </section>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch6">
                                        <h4 class="h6 text-brand mb-2">Chapter Six: “Sustaining Action”</h4>
                                        <p>
                                            Implementing strategies and maintaining momentum show the difficulty in sustaining attention and effort by the numerous and diverse individuals and agencies, most of whom are independent of each other.
                                        </p>
                                        
                                        <p>
                                            Public leaders act as catalysts to gain support and legitimacy for the multiple strategies.
                                            They network to ensure that supportive coalitions will advocate and champion continued implementation.
                                            They focus on the ultimate goal, outcome, or desired result.
                                            Catalysts help to develop an outcome-based information system to monitor progress and acknowledge small wins.
                                        </p>
                                        
                                        <p class="mb-0">
                                            Energy is needed to sustain movement toward achieving an outcome, while also providing feedback that stimulates policy learning and adjusting.
                                            A form of cohesion holds the key implementers together without controlling or coercing the participants.
                                            The professional relationships developed by the participants builds trust, the informational infrastructure creates more cohesion holding the key stakeholders together over the long term.
                                        </p>
                                    </section>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch7">
                                        <h4 class="h6 text-brand mb-2">Chapter Seven: “Thinking and Acting Strategically”</h4>
                                        <p class="mb-2">Thinking strategically involves four distinct sets of analytical skills:</p>
                                        <ol class="mb-3">
                                            <li>Framing and reframing issues and their strategic responses.</li>
                                            <li>Identifying and defining end-outcomes or desired results</li>
                                            <li>Assessing stakeholder’s interests to discover common and complementary interests</li>
                                            <li>Systemic thinking to reveal interconnections and strategic leverage points</li>
                                        </ol>
                                        
                                        <p>
                                            One important technique is to clarify and simplify an issue by eliminating obscure terminology, define the problem in terms the public can understand and support.
                                        </p>
                                        
                                        <p class="mb-2">Common obstacles to strategic thinking:</p>
                                        <ul class="mb-0">
                                            <li>Failure to analyze</li>
                                            <li>Personality preferences (Myers Briggs Sensing vs. Intuitive)</li>
                                            <li>Emotional barriers</li>
                                        </ul>
                                    </section>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch8">
                                        <h4 class="h6 text-brand mb-2">Chapter Eight: “Facilitating Productive Working Groups”</h4>
                                        <p>
                                            This is the longest chapter in the book, filled with techniques, ideas, diagrams and tables of information to assist anyone facilitating or acting as a catalyst in a work group.
                                            Seventeen pages are dedicated to dealing with conflict.
                                        </p>
                                        
                                        <p>
                                            Collaboration and consensus are a challenge to achieve.
                                            Getting people to agree on a set of multiple strategies requires open-ended discussions of key issues and even intense arguments and debates by opposing interests.
                                            A group must build agreement and sustain implementation.
                                            A catalyst must understand the jargon; acknowledge competing points of view or stakes in the issue; draw truth out of poorly stated positions; synthesize statements; and actively integrate varied interests.
                                        </p>
                                        
                                        <p class="mb-2">“Leading from the middle” requires:</p>
                                        <ol class="mb-3">
                                            <li>Generating fresh ideas and new insights</li>
                                            <li>Coping with conflict</li>
                                            <li>Getting a group unstuck and moving the debate forward</li>
                                            <li>Forging multiple agreements</li>
                                        </ol>
                                        
                                        <p>
                                            Psychological research is clear: people’s minds are seldom changed once perspectives are well-formed.
                                            Therefore to expand people’s perspectives a catalytic leader doesn’t have to have the right answers but know to ask the right questions.
                                            As a catalytic leader one must shape the working group’s journey of discovery by the questions asked.
                                        </p>
                                        
                                        <ul class="mb-0">
                                            <li>Ask questions that direct attention to the desired future results, not past failures.</li>
                                            <li>Uncover motives and reveal causal assumptions by asking what or how?</li>
                                            <li>Ask questions that broaden options</li>
                                        </ul>
                                    </section>
                                    
                                    <hr class="my-4">
                                    
                                    <section id="cl-ch9">
                                        <h4 class="h6 text-brand mb-2">Chapter Nine: Leading from Personal Passion and Strength of Character</h4>
                                        <p>Public Leadership requires individuals to act as catalysts. Successful catalysts are often described as:</p>
                                        
                                        <ul class="mb-3">
                                            <li>Optimistic - convinced workable strategies are possible.</li>
                                            <li>Enthusiastic and persistent in the face of complex interrelationships a certain doggedness.</li>
                                            <li>Good humored - which is critical to maintain perspective in the heat of conflict.</li>
                                        </ul>
                                        
                                        <p>
                                            Character closely resembles personality; character is deeper, containing moral overtones and qualities.
                                            Character is defined as a pattern of behavior and actions consistent with an individual through time, motivated by internal dispositions that define the individual, particularly in contrast to other people.
                                        </p>
                                        
                                        <p class="mb-2">Characteristics of a Catalytic Leader are:</p>
                                        <ul class="mb-3">
                                            <li>Passion for results</li>
                                            <li>Sense of connectedness and relatedness</li>
                                            <li>Exemplary personal integrity</li>
                                        </ul>
                                        
                                        <p>
                                            Personal integrity and trust are built on the bedrock of core commitments, inner strength and a commitment to right conduct.
                                            Developing these habits occurs through a refinement of perception, reflection, feeling and action through repeated efforts.
                                        </p>
                                        
                                        <p>
                                            Catalytic leaders have a sincere commitment to the ideal and a spirit of inquiry, which involves regular examination of the ethical impact of one’s actions.
                                            This spirit of inquiry is propelled by a constant and unending process of self-cultivation that transforms the catalyst’s conduct throughout life.
                                        </p>
                                        
                                        <p class="mb-0">
                                            The Luke Center for Catalytic Leadership is grateful to Liz Burrows, Alumnus, Pacific Program Class of 2007 for completing this Executive Summary Overview.
                                        </p>
                                    </section>
                                    
                                    <div class="mt-4 d-flex justify-content-between align-items-center">
                                        <a class="small text-decoration-none text-brand link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="#clTop">Back to top</a>
                                        <span class="small text-secondary">Executive Summary Overview</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php 
                    // Insert HTML lower main section.
                    require app_public_path('lowerMainSection.php', APP_ENVIRONMENT);
                ?>                
            </div>
        </div>
    </div>
</section>
<?php
    // Insert HTML footer.
    require app_public_path('footer.php', APP_ENVIRONMENT);
?>
