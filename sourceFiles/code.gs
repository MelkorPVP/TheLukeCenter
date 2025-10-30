/*** CONFIG ***/
const WEBSITE_VALUES_SPREADSHEET_ID = '1svvvowqpORvHjcLV879Zy1A7TcM5QwIVZJYQYZgh144';
const CONTACT_DATA_SPREADSHEET_ID = '1HfTbML883LlMw4GVNL4vOnJfEjvkTA7-TLm6jiE1pSk';
const APPLICATION_DATA_SPREADSHEET_ID = '1eN8nuhkzau4xcyKoYLI26nQMmQ7wzDlI5iEleok-1sc';
const EMAIL_TO = ['admin@thelukecenter.org']; 

/*** WEB ***/
function doGet(event) 
{
    const data = getInitData();
    const template = HtmlService.createTemplateFromFile('index');
    template.init = data;

    return template.evaluate().setTitle('The Luke Center For Catalytic Leadership').setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

function include(name) 
{
  return HtmlService.createHtmlOutputFromFile(name).getContent();
}

/*** READ INITIAL DATA ***/
function getInitData()
{
  const cache = CacheService.getScriptCache();
  const key = 'init:v1';
  const cached = cache.get(key);
  if (cached) return JSON.parse(cached);


  const spreadsheet = SpreadsheetApp.openById(WEBSITE_VALUES_SPREADSHEET_ID); 
  const sheet = spreadsheet.getSheetByName('Values');

  if (!sheet) throw new Error('Missing Sheet: Values');
  const values = sheet.getRange(1,1,sheet.getLastRow(),2).getValues()
  const object = {};
  for (let i = 0; i < values.length; i++) 
  {
    const key = values[i][0];
    const val = values[i][1];
    if (key) object[String(key).trim()] = val;
  }

  cache.put(key, JSON.stringify(object), 300); // 5 minutes

  return object; 
}

/*** Contact Submission ***/
function submitContactForm(payload) 
{
  // allowlist + trim
  const allowed = ['contactFirstName','contactLastName','contactEmail','contactPhone','contactPhoneType','currentWork','yearAttended'];
  const data = {};
  allowed.forEach(f => data[f] = (payload?.[f] ?? '').toString().trim());

  if (!data.contactFirstName || !data.contactEmail) 
  {
    throw new Error('Name and Email are required.');
  }

  // append to sheet
  const spreadsheet = SpreadsheetApp.openById(CONTACT_DATA_SPREADSHEET_ID);
  const sheet = spreadsheet.getSheetByName('Submissions') || spreadsheet.insertSheet('Submissions');
  if (sheet.getLastRow() === 0) {
    sheet.appendRow(['Timestamp','FirstName','LastName','Email','Phone','PhoneType','CurrentWorkOrg','ProgramYear']);
  }
  sheet.appendRow([new Date(), data.contactFirstName, data.contactLastName, data.contactEmail, data.contactPhone, data.contactPhoneType, data.currentWork, data.yearAttended]);

  // email
  const subject = `New Contact Info Update: ${data.contactFirstName + ' ' + data.contactLastName}`;
  const htmlBody =
    `<div style="font-family:Arial,sans-serif">
       <h1>New submission</h1>
        <p>
          <b>First Name:</b> ${escapeHtml(data.contactFirstName)}<br>
          <b>Last Name:</b> ${escapeHtml(data.contactLastName)}<br>
          <b>Email:</b> ${escapeHtml(data.contactEmail)}<br>
          <b>Phone:</b> ${escapeHtml(data.contactPhone)}<br>
          <b>Current Organization:</b> ${escapeHtml(data.currentWork)}<br>
          <b>Program Year:</b> ${escapeHtml(data.yearAttended)}         
        </p>
     </div>`;

  EMAIL_TO.forEach(to => MailApp.sendEmail({
    to,
    subject,
    htmlBody
  }));

  return { ok:true };
}
/*** Application Submission ***/
function submitApplicationForm(payload) 
{
  // allowlist + trim
  const allowed = ['applicantFirstName','applicantLastName','applicantPreferredName','applicantPronouns','applicantEmail','applicantPhone','applicantPhoneType',
  'addressOne','addressTwo','city','state','zip','vegan','vegetarian','diet','accommodations','org','title','supName','supEmail','supPhone','refferalQuestion','questionOne',
  'questionTwo','questionThree','questionFour','partialScholarship','assistAmount'];
  const data = {};
  allowed.forEach(f => data[f] = (payload?.[f] ?? '').toString().trim());

  if (!data.applicantFirstName || !data.applicantLastName || !data.applicantEmail || !data.applicantPhone || !data.applicantPhoneType || !data.addressOne || !data.city || !data.state 
  || !data.zip || !data.vegan || !data.vegetarian || !data.org || !data.title || !data.supName || !data.supEmail || !data.supPhone || !data.refferalQuestion || !data.questionOne 
  || !data.questionTwo || !data.questionThree || !data.questionFour) 
  {
    throw new Error('Invalid form submission, missing one or more required fields.');
  }

  // append to sheet
  const spreadsheet = SpreadsheetApp.openById(APPLICATION_DATA_SPREADSHEET_ID);
  const sheet = spreadsheet.getSheetByName('Submissions') || spreadsheet.insertSheet('Submissions');
  if (sheet.getLastRow() === 0) {
    sheet.appendRow(['Timestamp','ApplicantFirst','ApplicantLast','PreferredName','Pronouns','ApplicantEmail','ApplicantPhone','PhoneType',
    'StreetAddressOne','StreetAddressTwo','City','StateProvince','ZipPostalCode','Vegan','Vegetarian','Restrictions','AccessibilityNeeds','OrganizationAgency','TitleRole','SponsorName','SponsorEmail','SponsorPhone','QuestionsQ1','QuestionsQ2','QuestionsQ3','QuestionsQ4','QuestionsQ5','ScholarshipQ1','ScholarshipQ2']);
  }
  sheet.appendRow([new Date(), data.applicantFirstName, data.applicantLastName, data.applicantPreferredName, data.applicantPronouns, data.applicantEmail, data.applicantPhone, data.applicantPhoneType, data.addressOne, data.addressTwo, data.city, data.state, data.zip, data.vegan, data.vegetarian, data.diet, data.accommodations, data.org, data.title, data.supName, data.supEmail, data.supPhone, data.refferalQuestion, data.questionOne, data.questionTwo, data.questionThree, data.questionFour, data.partialScholarship, data.assistAmount]);

  // email
  const subject = `New Application Submission: ${data.applicantFirstName + ' ' + data.applicantLastName}`;
  const htmlBody =
    `<div style="font-family:Arial,sans-serif">
       <h1>New Application</h1>
       <hr>
       <h2>Applicant Information</h2>
        <p>
          <b>First Name:</b> ${escapeHtml(data.applicantFirstName)}<br>
          <b>Last Name:</b> ${escapeHtml(data.applicantLastName)}<br>
          <b>Preffered Name:</b> ${escapeHtml(data.applicantPreferredName)}<br>
          <b>Pronouns:</b> ${escapeHtml(data.applicantPronouns)}<br>
          <b>Email:</b> ${escapeHtml(data.applicantEmail)}<br>
          <b>Phone:</b> ${escapeHtml(data.applicantPhone)}<br> 
          <b>Phone Type:</b> ${escapeHtml(data.applicantPhoneType)}
        </p> 
        <hr>      
       <h2>Mailing Address</h2>
        <p>
          <b>Street Address One:</b> ${escapeHtml(data.addressOne)}<br>
          <b>Street Address Two:</b> ${escapeHtml(data.addressTwo)} <br>
          <b>City:</b> ${escapeHtml(data.city)} <br>
          <b>State/Province:</b> ${escapeHtml(data.state)} <br>
          <b>Zip Code:</b> ${escapeHtml(data.zip)}
        </p>
        <hr>
       <h2>Personal Needs</h2>
       <p>
        <b>Vegan:</b> ${escapeHtml(data.vegan)}<br>
        <b>Vegetarian:</b> ${escapeHtml(data.vegetarian)}<br>
        <b>Other Restrictions:</b> ${escapeHtml(data.diet)}<br>
        <b>Accessibility Needs:</b> ${escapeHtml(data.accommodations)}
       </p>
       <hr>
       <h2>Organization & Role</h2>
       <p>
        <b>Organization/Agency:</b> ${escapeHtml(data.org)}<br>
        <b>Current Title/Role:</b> ${escapeHtml(data.title)}
       </p>
       <hr>
       <h2>Sponsor/Supervisor's Info</h2>
       <p>
        <b>Name:</b> ${escapeHtml(data.supName)}<br>
        <b>Email:</b> ${escapeHtml(data.supEmail)}<br>
        <b>Phone:</b> ${escapeHtml(data.supPhone)}
       </p>
       <hr>
       <h2>Questions</h2>
       <p>
        <b>How did you learn about the Pacific Program? If it was from a Pacific Program Alumni, please tell us who.</b> ${escapeHtml(data.refferalQuestion)}<br>
        <b>Describe the range of your leadership responsibilities in your current position.</b> ${escapeHtml(data.questionOne)}<br>
        <b>Describe your experience working with a variety of groups (public sector, private sector, non-profits) to create or sustain partnerships.</b> ${escapeHtml(data.questionTwo)}<br>
        <b>Describe a professional challenge that you face that you would be interested in networking with other Pacific Program Participants about.</b> ${escapeHtml(data.questionThree)}<br>
        <b>Describe how participating in the Pacific Program will help you achieve your professional goals and how you'll use the learning in your personal and professional life.</b> ${escapeHtml(data.questionFour)}       
      </p>
      <hr>
       <h2>Scholarship Request</h2>
       <p>
        <b>Will a partial scholarship impact your ability to attend the Pacific Program?</b> ${escapeHtml(data.partialScholarship)}<br>
        <b>What amount of financial assistance are you requesting?</b> ${escapeHtml(data.assistAmount)}
       </p>
      <hr>
     </div>`;

  EMAIL_TO.forEach(to => MailApp.sendEmail({
    to,
    subject,
    htmlBody
  }));

  return { ok:true };
}

/*** UTIL ***/
function escapeHtml(s) 
{
  return String(s)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}