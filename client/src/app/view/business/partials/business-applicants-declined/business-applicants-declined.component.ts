import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { BusinessService } from '../../../../services/business.service';
import { SharedService } from '../../../../services/shared.service';
import { BusinessApplicant, JobCriteria, ApplicantsList } from '../../../../../entities/models';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute, Router } from '@angular/router';
import { IMultiSelectOption, IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { articles } from '../../../../constants/articles.const';
import { CookieService } from 'ngx-cookie-service';
import { FormControl, FormGroup } from '@angular/forms';
import { IMonthCalendarConfigInternal } from 'ng2-date-picker/month-calendar/month-calendar-config';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-business-applicants-declined',
  templateUrl: './business-applicants-declined.component.html',
  styleUrls: ['./business-applicants-declined.component.scss']
})
export class BusinessApplicantsDeclinedComponent implements OnInit {

  @ViewChild('rendering') _rendering: ElementRef;
  @ViewChild('openFilters') _openFilters: ElementRef;
  @ViewChild('openButton') _openButton: ElementRef;
  @ViewChild('filterFont') _filterFont: ElementRef;
  @ViewChild('filterItem') _filterItem: ElementRef;

  public applicantsDeclined = new Array<BusinessApplicant>();

  public listOfJobs: JobCriteria[];
  public applicantsList: ApplicantsList;

  public modalActiveClose;
  public candidateToView;
  public requestJobId: number;

  public preloaderPage = true;
  public totalCount = {
    number: 0
  };
  public totalCountFilter: number;
  public renderingApplicants: boolean;

  public articles = articles;
  public articlesFirmPredefined: string[] = [];
  public articlesFirmSelectedName: string[];
  public articlesFirmSettings: IMultiSelectSettings = {
    displayAllSelectedText: true,
    selectionLimit: 0,
    showCheckAll: true,
    showUncheckAll: true,
  };
  public articlesFirmOptions: IMultiSelectOption[] = [];
  public articlesFirmTextConfig: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Articles firm',
    allSelected: 'All selected',
  };

  public checkOpenFilters = false;

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = true;

  public filterForm: FormGroup;

  public config: IMonthCalendarConfigInternal;
  public selectedDateStart: string = '';
  public selectedDateEnd: string = '';

  public genderModel = [];
  public genderOptions: IMultiSelectOption[];

  public availabilityModel = [];
  public availabilityOptions: IMultiSelectOption[];

  public ethnicityModel = [];
  public ethnicityOptions: IMultiSelectOption[];

  public locationModel = [];
  public locationOptions: IMultiSelectOption[];

  public qualificationModel = [];
  public qualificationOptions: IMultiSelectOption[];

  public nationalityModel = [];
  public nationalityOptions: IMultiSelectOption[];

  public configGender: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Gender',
    allSelected: 'All selected - Gender',
  };
  public configAvailability: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Availability',
    allSelected: 'All selected - Availability',
  };
  public configEthnicity: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Ethnicity',
    allSelected: 'All selected - Ethnicity',
  };
  public configLocation: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Location',
    allSelected: 'All selected - Location',
  };
  public configQualification: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Qualification',
    allSelected: 'All selected - Qualification',
  };
  public configNationality: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Nationality',
    allSelected: 'All selected - Nationality',
  };

  constructor(
    private readonly _businessService: BusinessService,
    private readonly _sharedService: SharedService,
    private readonly _modalService: NgbModal,
    private readonly _route: ActivatedRoute,
    private readonly _cookieService: CookieService,
    private readonly _router: Router,
    private readonly _toastr: ToastrService
  ) {
    this._sharedService.checkSidebar = false;

    this._route.queryParams.subscribe(data => {
      if (data.jobId !== undefined) {
        this.requestJobId = Number(data.jobId);
      }
      else {
        this.requestJobId = null;
      }
    });

    this.genderOptions = [
      { id: 'Male', name: 'Male' },
      { id: 'Female', name: 'Female' }
    ];
    this.availabilityOptions = [
      { id: 1, name: 'Immediately' },
      { id: 2, name: 'Within 1 calendar month' },
      { id: 3, name: 'Within 3 calendar months' }
    ];
    this.ethnicityOptions = [
      { id: 'Black', name: 'Black' },
      { id: 'White', name: 'White' },
      { id: 'Coloured', name: 'Coloured' },
      { id: 'Indian', name: 'Indian' },
      { id: 'Oriental', name: 'Oriental' }
    ];
    this.locationOptions = [
      { id: 'Johannesburg', name: 'Johannesburg' },
      { id: 'Cape Town', name: 'Cape Town' },
      { id: 'Pretoria', name: 'Pretoria' },
      { id: 'Durban', name: 'Durban' },
      { id: 'International', name: 'International' },
      { id: 'Other', name: 'Other' }
    ];
    this.qualificationOptions = [
      { id: 1, name: 'Newly qualified CA' },
      { id: 2, name: 'Part qualified CA' }
    ];
    this.nationalityOptions = [
      { id: 1, name: 'South African Citizen (BBBEE)' },
      { id: 2, name: 'South African Citizen (Non-BBBEE)' },
      { id: 3, name: 'Non-South African (With Permit)' },
      { id: 4, name: 'Non-South African (Without Permit)' }
    ];
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.filterForm = new FormGroup({
      articlesFirm: new FormControl(''),
      gender: new FormControl(''),
      ethnicity: new FormControl(''),
      qualification: new FormControl(''),
      video: new FormControl(null),
      availability: new FormControl(''),
      location: new FormControl(''),
      nationality: new FormControl(''),
      articlesCompletedStart: new FormControl(''),
      articlesCompletedEnd: new FormControl(''),
    });

    this.articles.forEach((article) => {
      this.articlesFirmOptions.push({ id: article, name: article });
    });

    if (this._cookieService.get('rendering') === 'false') {
      this.renderingApplicants = false;
      this.statusRendiring(this.renderingApplicants);
    } else if (this._cookieService.get('rendering') === 'true'){
      this.renderingApplicants = true;
      this.statusRendiring(this.renderingApplicants);
    } else {
      this.renderingApplicants = false;
      this.statusRendiring(this.renderingApplicants);
    }

    this.fetchListOfApplicants(this.requestJobId, null, '', '', '', '', '', '', '', '', null, '', '', '').then(response => {
      this.fetchAllJobs().then(() => {
        this.onResize();
      });
    });
  }

  /**
   * Reset filters
   */
  public resetFilterForm(): void{
    try {
      this.articlesFirmPredefined = [];
      this.articlesFirmSelectedName = [];
      this.genderModel = [];
      this.availabilityModel = [];
      this.ethnicityModel = [];
      this.locationModel = [];
      this.qualificationModel = [];
      this.nationalityModel = [];

      this.filterForm.setValue({
        articlesFirm: [],
        gender: [],
        ethnicity: [],
        qualification: [],
        video: null,
        availability: [],
        location: [],
        nationality: [],
        articlesCompletedStart: '',
        articlesCompletedEnd: ''
      });

      setTimeout(() => {
        this.getApplicantsCount(this.requestJobId, null, '', '', '', '', '', '', '', '', null, '', '', '');
      }, 500);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Hide articles firm
   * @param elem
   */
  public hideArticlesFirm(elem): void {
    let nextSibling = elem.nextSibling;
    while(nextSibling && nextSibling.nodeType != 1) {
      nextSibling = nextSibling.nextSibling
    }
    nextSibling.style.opacity = 0;
    elem.style.opacity = 1;
  }

  /**
   * Reset Array
   */
  public resetArrayPagination(): void{
    this.applicantsDeclined = [];
    this.pagination = 1;
  }

  /**
   * Load pagination
   */
  public loadPagination(jobID: number, page, limit, search, articlesFirm, gender, ethnicity, nationality, location, qualification, video, availability, articlesCompletedStart, articlesCompletedEnd): void {
    this.pagination++;
    this.paginationLoader = true;
    this.searchFilter(jobID, page, limit, search, articlesFirm, gender, ethnicity, nationality, location, qualification, video, availability, articlesCompletedStart, articlesCompletedEnd);
  }

  /**
   * Open more filters
   */
  public openMoreFilters(): void {

    this.checkOpenFilters = !this.checkOpenFilters;

    if (this.checkOpenFilters === true) {
      this._openButton.nativeElement.innerHTML = 'Close more filters';
      this._openFilters.nativeElement.classList.add('active');
      this._filterFont.nativeElement.classList.add('active');
      this._filterItem.nativeElement.classList.add('active');
    }
    else {
      this._openButton.nativeElement.innerHTML = 'Open more filters';
      this._openFilters.nativeElement.classList.remove('active');
      this._filterFont.nativeElement.classList.remove('active');
      this._filterItem.nativeElement.classList.remove('active');
    }
  }

  /**
   * Resize window
   */
  public onResize(): void{
    if(window.innerWidth <= 1024){
      this.renderingApplicants = false;
      this.statusRendiring(false);
    }
  }

  /**
   * Status rendering
   * @param status {boolean}
   */
  public statusRendiring(status: boolean): void {
    if (status === true){
      this._rendering.nativeElement.classList.remove('cell-applicant');
    }
    else if (status === false) {
      this._rendering.nativeElement.classList.add('cell-applicant');
    }
    this._cookieService.set('rendering', String(status));
    this.renderingApplicants = status;
  }

  /**
   * opens popup
   * @param content - content to be placed within
   * @param candidate - candidateId id to show within popup
   */
  public openVerticallyCentered(content: any, candidate) {
    this.candidateToView = candidate;

    this.modalActiveClose = this._modalService.open(content, { centered: true, size: 'lg', windowClass: 'xlModal' });
  }

  /**
   * opens popup
   * @param content - content to be placed within
   */
  public openVerticallyCenter(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, size: 'lg', windowClass: 'xlModal' });
  }

  /**
   * Fetches list of all applicants
   * @returns void
   */
  public async fetchListOfApplicants(jobID: number, page, limit, search, articlesFirm, gender, ethnicity, nationality, location, qualification, video, availability, articlesCompletedStart, articlesCompletedEnd): Promise<void> {

    if (this.genderOptions.length === this.genderModel.length) {
      gender = 'All';
    }

    if (this.qualificationOptions.length === this.qualificationModel.length) {
      qualification = 'All';
    }

    if (this.nationalityOptions.length === this.nationalityModel.length) {
      nationality = 'All';
    }

    if (this.ethnicityOptions.length === this.ethnicityModel.length) {
      ethnicity = 'All';
    }

    if (this.locationOptions.length === this.locationModel.length) {
      location = 'All';
    }

    if (this.availabilityOptions.length === this.availabilityModel.length) {
      availability = 'All';
    }

    const data = {
      jobID: jobID,
      page: this.pagination,
      limit: limit,
      search: search,
      articlesFirm: articlesFirm,
      gender: gender,
      ethnicity: ethnicity,
      nationality: nationality,
      location: location,
      qualification: qualification,
      video: video,
      availability: availability,
      articlesCompletedStart: articlesCompletedStart,
      articlesCompletedEnd: articlesCompletedEnd
    };

    const startDate = new Date(articlesCompletedStart);
    const endDate = new Date(articlesCompletedEnd);
    if (startDate > endDate) {
      this._toastr.error('Date Articles Completed End not be shorter than the Date Articles Completed Start');
    }
    else{
      try {
        const response = await this._businessService.getApplicantsDeclined(data);

        response.items.forEach((item) => {
          item.dateAvailability = this._sharedService.getCandidateAvailabilityInHumanReadableForm(
            item.availability, item.availabilityPeriod, item.dateAvailability
          );
          this.applicantsDeclined.push(item);
        });

        if (response.pagination.total_count === this.applicantsDeclined.length) {
          this.loadMoreCheck = false;
        }
        else if (response.pagination.total_count !== this.applicantsDeclined.length) {
          this.loadMoreCheck = true;
        }
        this.paginationLoader = false;

        this.totalCount.number = response.pagination.total_count;
        this.totalCountFilter = response.pagination.total_count;
        setTimeout(() => {
          this.preloaderPage = false;
        }, 500);
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Search filters
   * @param jobID {number}
   * @param page {number}
   * @param limit {string}
   * @param search {string}
   * @param articlesFirm {string}
   * @param gender {string}
   * @param ethnicity {string}
   * @param nationality {string}
   * @param location {string}
   * @param qualification {number}
   * @param video {number}
   * @param availability {number}
   * @param articlesCompletedStart {string}
   * @param articlesCompletedEnd {string}
   * @return {Promise<void>}
   */
  public async searchFilter(jobID, page, limit, search, articlesFirm, gender, ethnicity, nationality, location, qualification, video, availability, articlesCompletedStart, articlesCompletedEnd): Promise<void> {

    if (jobID === undefined) {
      jobID = null;
    }
    /*if (jobID.$ngOptionValue === null){
      jobID = null;
    }*/
    if (this.genderOptions.length === this.genderModel.length) {
      gender = 'All';
    }

    if (this.qualificationOptions.length === this.qualificationModel.length) {
      qualification = 'All';
    }

    if (this.nationalityOptions.length === this.nationalityModel.length) {
      nationality = 'All';
    }

    if (this.ethnicityOptions.length === this.ethnicityModel.length) {
      ethnicity = 'All';
    }

    if (this.locationOptions.length === this.locationModel.length) {
      location = 'All';
    }

    if (this.availabilityOptions.length === this.availabilityModel.length) {
      availability = 'All';
    }

    if(jobID !== null) {
      this.requestJobId = jobID;
    }
    this.preloaderPage = false;
    this.articlesFirmSelectedName = (this.articlesFirmPredefined.length === this.articles.length)
      ? new Array('All')
      : this.articlesFirmPredefined;
    try {
      this.fetchListOfApplicants(jobID, page, limit, search, this.articlesFirmSelectedName.join(','), gender, ethnicity, nationality, location, qualification, video, availability, articlesCompletedStart, articlesCompletedEnd);
      this._openButton.nativeElement.innerHTML = 'Open more filters';
      this._openFilters.nativeElement.classList.remove('active');
      this._filterFont.nativeElement.classList.remove('active');
      this._filterItem.nativeElement.classList.remove('active');
      this.checkOpenFilters = false;

    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Search count
   * @param jobID {number}
   * @param page {number}
   * @param limit {string}
   * @param search {string}
   * @param articlesFirm {string}
   * @param gender {string}
   * @param ethnicity {string}
   * @param nationality {string}
   * @param location {string}
   * @param qualification {number}
   * @param video {number}
   * @param availability {number}
   * @param articlesCompletedStart {string}
   * @param articlesCompletedEnd {string}
   * @return {Promise<void>}
   */
  public async getApplicantsCount(jobID, page, limit, search, articlesFirm, gender, ethnicity, nationality, location, qualification, video, availability, articlesCompletedStart, articlesCompletedEnd): Promise<void> {

    if (jobID === undefined) {
      jobID = null;
    }
    /*if (jobID.$ngOptionValue === null){
      jobID = null;
    }*/
    if (this.genderOptions.length === this.genderModel.length) {
      gender = 'All';
    }

    if (this.qualificationOptions.length === this.qualificationModel.length) {
      qualification = 'All';
    }

    if (this.nationalityOptions.length === this.nationalityModel.length) {
      nationality = 'All';
    }

    if (this.ethnicityOptions.length === this.ethnicityModel.length) {
      ethnicity = 'All';
    }

    if (this.locationOptions.length === this.locationModel.length) {
      location = 'All';
    }

    if (this.availabilityOptions.length === this.availabilityModel.length) {
      availability = 'All';
    }

    if (this.articlesFirmSelectedName === null) {
      this.articlesFirmSelectedName = [];
      this.articlesFirmPredefined = [];
    }
    else {
      this.articlesFirmSelectedName = (this.articlesFirmPredefined.length === this.articles.length)
        ? new Array('All')
        : this.articlesFirmPredefined;
    }
    const data = {
      jobID: jobID,
      page: this.pagination,
      limit: limit,
      search: search,
      articlesFirm: this.articlesFirmSelectedName.join(','),
      gender: gender,
      ethnicity: ethnicity,
      nationality: nationality,
      location: location,
      qualification: qualification,
      video: video,
      availability: availability,
      articlesCompletedStart: articlesCompletedStart,
      articlesCompletedEnd: articlesCompletedEnd
    };

    const startDate = new Date(articlesCompletedStart);
    const endDate = new Date(articlesCompletedEnd);
    if (startDate > endDate) {
      this._toastr.error('Date Articles Completed End not be shorter than the Date Articles Completed Start');
    }
    else{
      try {
        const response = await this._businessService.getApplicantsCount(4, data);
        this.totalCountFilter = response.countApplicants;
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Fetches list of jobs
   * @returns void
   */
  public async fetchAllJobs(): Promise<void> {
    try {
      this.listOfJobs = await this._businessService.getBusinessJobsMatchingCriteria(false, null);
      this.preloaderPage = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Select change router
   * @param url
   * @param id
   */
  public routerApplicants(url, id): void {
    this._router.navigate([url], (id > 0) ? { queryParams: { jobId: id } } : {});
  }
}
