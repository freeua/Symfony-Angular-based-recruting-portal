import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { IMultiSelectOption, IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { articles } from '../../../../constants/articles.const';
import { CandidateApprove } from '../../../../../entities/models-admin';
import { AdminService } from '../../../../services/admin.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';
import { Angular5Csv } from 'angular5-csv/Angular5-csv';
import { FormControl, FormGroup } from '@angular/forms';
import { IMonthCalendarConfigInternal } from 'ng2-date-picker/month-calendar/month-calendar-config';

@Component({
  selector: 'app-admin-all-candidates',
  templateUrl: './admin-all-candidates.component.html',
  styleUrls: ['./admin-all-candidates.component.scss']
})
export class AdminAllCandidatesComponent implements OnInit {

  @ViewChild('openFilters') _openFilters: ElementRef;
  @ViewChild('openButton') _openButton: ElementRef;
  @ViewChild('filterFont') _filterFont: ElementRef;
  @ViewChild('filterItem') _filterItem: ElementRef;

  public approveCandidateList = Array<CandidateApprove>();

  public modalActiveClose: any;
  public selectedId: number;

  public articles = articles;
  public articlesFirmPredefined: string[] = [];
  public articlesFirmSelectedName: string[] = [];
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

  public preloaderPage = true;
  public checkOpenFilters = false;
  public candidatesCountMatchingCriteria: number;

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

  public criminalModel = [];
  public criminalOptions: IMultiSelectOption[];

  public creditModel = [];
  public creditOptions: IMultiSelectOption[];

  public enabledModel = [];
  public enabledOptions: IMultiSelectOption[];

  public profileModel = [];
  public profileOptions: IMultiSelectOption[];

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
  public configCriminal: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Criminal',
    allSelected: 'All selected - Criminal',
  };
  public configCredit: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Credit',
    allSelected: 'All selected - Credit',
  };
  public configEnabled: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Enabled',
    allSelected: 'All selected - Enabled',
  };
  public configProfile: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Profile Completed',
    allSelected: 'All selected - Profile Completed',
  };

  constructor(
    private readonly _adminService: AdminService,
    private readonly _modalService: NgbModal,
    private readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService
  ) {
    this._sharedService.checkSidebar = false;

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
    this.criminalOptions = [
      { id: true, name: 'Yes' },
      { id: false, name: 'No' }
    ];
    this.creditOptions = [
      { id: true, name: 'Yes' },
      { id: false, name: 'No' }
    ];
    this.enabledOptions = [
      { id: true, name: 'Yes' },
      { id: false, name: 'No' }
    ];
    this.profileOptions = [
      { id: true, name: 'Yes' },
      { id: false, name: 'No' }
    ];
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.filterForm = new FormGroup({
      articlesFirm: new FormControl(''),
      gender: new FormControl(''),
      qualification: new FormControl(''),
      nationality: new FormControl(''),
      ethnicity: new FormControl(''),
      //video: new FormControl(null),
      location: new FormControl(''),
      availability: new FormControl(''),
      criminal: new FormControl(''),
      credit: new FormControl(''),
      enabled: new FormControl(true),
      articlesCompletedStart: new FormControl(''),
      articlesCompletedEnd: new FormControl(''),
      profileComplete: new FormControl('All')
    });

    this.articles.forEach((article) => {
      this.articlesFirmOptions.push({ id: article, name: article });
    });

    setTimeout(() => {
      this.approveCandidateList = [];
      this.getAllCandidateList('', '', '', '', '', '', '', '', '', '', '', true, '', '', 'All');
    }, 500);
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
      this.creditModel = [];
      this.criminalModel = [];
      this.enabledModel = [];
      this.profileModel = [];

      this.filterForm.setValue({
        articlesFirm: [],
        gender: null,
        qualification: null,
        nationality: null,
        ethnicity: null,
        //video: null,
        location: null,
        availability: null,
        criminal: null,
        credit: null,
        enabled: true,
        articlesCompletedStart: '',
        articlesCompletedEnd: '',
        profileComplete: null,
      });

      setTimeout(() => {
        this.getAllCandidateListCount('', '', '', '', '', '', '', '', '', '', '', true, '', '', null);
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
  }

  /**
   * Export CSV file
   * @param search
   * @param articlesFirm
   * @param nationality
   * @param ethnicity
   * @param gender
   * @param qualification
   * @param location
   * @param criminal
   * @param credit
   * @param availability
   * @param video
   * @param enabled
   * @param articlesCompletedStart
   * @param articlesCompletedEnd
   * @param profileComplete
   * @return {Promise<void>}
   */
  public async exportDataCSV(search, articlesFirm, nationality, ethnicity, gender, qualification, location, criminal, credit, availability, video, enabled, articlesCompletedStart, articlesCompletedEnd, profileComplete): Promise<void>{

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

    if (this.criminalOptions.length === this.criminalModel.length) {
      criminal = 'All';
    }

    if (this.creditOptions.length === this.creditModel.length) {
      credit = 'All';
    }

    if (this.enabledOptions.length === this.enabledModel.length) {
      enabled = 'All';
    }

    if (this.profileOptions.length === this.profileModel.length) {
      profileComplete = 'All';
    }

    this.articlesFirmSelectedName = [];
    this.articlesFirmPredefined.forEach((index,i) => {
      this.articlesFirmSelectedName.push(this.articlesFirmPredefined[i]);
    });
    this.articlesFirmSelectedName = (this.articlesFirmSelectedName.length === this.articles.length)
      ? new Array('All')
      : this.articlesFirmSelectedName;
    articlesFirm = (this.articlesFirmSelectedName.length > 0) ? this.articlesFirmSelectedName.join(',') : 'All';

    const params = {
      search: search,
      articlesFirm: articlesFirm,
      nationality: nationality,
      ethnicity: ethnicity,
      gender: gender,
      qualification: qualification,
      location: location,
      criminal: criminal,
      credit: credit,
      availability: availability,
      video: 0,
      enabled: enabled,
      page: this.pagination,
      csv: true,
      articlesCompletedStart: articlesCompletedStart,
      articlesCompletedEnd: articlesCompletedEnd,
      profileComplete: profileComplete
    };

    try {
      const response = await this._adminService.getAllCandidateList(params);

      const options = {
        showLabels: true,
        headers: ['Name', 'Articles Firm', 'Email' , 'Tel Number', 'Profile Completed', 'WhatsApp Integration', 'Referring Agent', 'Active']
      };

      new Angular5Csv(response, 'All candidates', options);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Managed candidate user
   * @param {CandidateApprove} user
   * @param {boolean} status
   * @return {Promise<void>}
   */
  public async managedCandidateUser(user: CandidateApprove, status: boolean): Promise<void> {
    try {
      await this._adminService.managedCandidateUser(user.id, status);

      const index = this.approveCandidateList.indexOf(user);
      this.approveCandidateList.splice(index, 1);
      this._toastr.success((status) ? 'Candidate has been approved' : 'Candidate has been declined');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Reset Array
   */
  public resetArrayPagination(): void{
    this.approveCandidateList = [];
    this.pagination = 1;
  }

  /**
   * Load pagination
   */
  public async loadPagination(search, articlesFirm, nationality, ethnicity, gender, qualification, location, criminal, credit, availability, video, enabled, articlesCompletedStart, articlesCompletedEnd, profileComplete): Promise<void> {

    this.pagination++;
    this.paginationLoader = true;
    this.getAllCandidateList(search, articlesFirm, nationality, ethnicity, gender, qualification, location, criminal, credit, availability, video, enabled, articlesCompletedStart, articlesCompletedEnd, profileComplete);
  }

  /**
   * Close more filters
   */
  public closeMoreFilters(): void{
    this.preloaderPage = true;
    this._openButton.nativeElement.innerHTML = 'Open more filters';
    this._openFilters.nativeElement.classList.remove('active');
    this._filterFont.nativeElement.classList.remove('active');
    this._filterItem.nativeElement.classList.remove('active');
    this.checkOpenFilters = false;
  }

  /**
   * Get all candidates
   * @param search
   * @param articlesFirm
   * @param nationality
   * @param ethnicity
   * @param gender
   * @param qualification
   * @param location
   * @param criminal
   * @param credit
   * @param availability
   * @param video
   * @param enabled
   * @param articlesCompletedStart
   * @param articlesCompletedEnd
   * @param profileComplete
   * @return {Promise<void>}
   */
  public async getAllCandidateList(search, articlesFirm, nationality, ethnicity, gender, qualification, location, criminal, credit, availability, video, enabled, articlesCompletedStart, articlesCompletedEnd, profileComplete): Promise<void> {
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

    if (this.criminalOptions.length === this.criminalModel.length) {
      criminal = 'All';
    }

    if (this.creditOptions.length === this.creditModel.length) {
      credit = 'All';
    }

    if (this.enabledOptions.length === this.enabledModel.length) {
      enabled = 'All';
    }

    if (this.profileOptions.length === this.profileModel.length) {
      profileComplete = 'All';
    }

    this.articlesFirmSelectedName = [];
    this.articlesFirmPredefined.forEach((index,i) => {
      this.articlesFirmSelectedName.push(this.articlesFirmPredefined[i]);
    });
    this.articlesFirmSelectedName = (this.articlesFirmSelectedName.length === this.articles.length)
      ? new Array('All')
      : this.articlesFirmSelectedName;
    articlesFirm = (this.articlesFirmSelectedName.length > 0) ? this.articlesFirmSelectedName.join(',') : 'All';

    const params = {
      search: search,
      articlesFirm: articlesFirm,
      nationality: nationality,
      ethnicity: ethnicity,
      gender: gender,
      qualification: qualification,
      location: location,
      criminal: criminal,
      credit: credit,
      availability: availability,
      video: video,
      enabled: enabled,
      page: this.pagination,
      articlesCompletedStart: articlesCompletedStart,
      articlesCompletedEnd: articlesCompletedEnd,
      profileComplete: profileComplete
    };

    const startDate = new Date(articlesCompletedStart);
    const endDate = new Date(articlesCompletedEnd);
    if (startDate > endDate) {
      this._toastr.error('Date Articles Completed End not be shorter than the Date Articles Completed Start');
    }
    else{
      try {
        this._openButton.nativeElement.innerHTML = 'Open more filters';
        this._openFilters.nativeElement.classList.remove('active');
        this._filterFont.nativeElement.classList.remove('active');
        this._filterItem.nativeElement.classList.remove('active');

        const response = await this._adminService.getAllCandidateList(params);

        response.items.forEach((item) => {
          this.approveCandidateList.push(item);
        });

        if (response.pagination.total_count === this.approveCandidateList.length) {
          this.loadMoreCheck = false;
        }
        else if (response.pagination.total_count !== this.approveCandidateList.length){
          this.loadMoreCheck = true;
        }

        this.preloaderPage = false;
        this.paginationLoader = false;
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Get all candidates
   * @param search
   * @param articlesFirm
   * @param nationality
   * @param ethnicity
   * @param gender
   * @param qualification
   * @param location
   * @param criminal
   * @param credit
   * @param availability
   * @param video
   * @param enabled
   * @param articlesCompletedStart
   * @param articlesCompletedEnd
   * @param profileComplete
   * @return {Promise<void>}
   */
  public async getAllCandidateListCount(search, articlesFirm, nationality, ethnicity, gender, qualification, location, criminal, credit, availability, video, enabled, articlesCompletedStart, articlesCompletedEnd, profileComplete): Promise<void> {
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

    if (this.criminalOptions.length === this.criminalModel.length) {
      criminal = 'All';
    }

    if (this.creditOptions.length === this.creditModel.length) {
      credit = 'All';
    }

    if (this.enabledOptions.length === this.enabledModel.length) {
      enabled = 'All';
    }

    if (this.profileOptions.length === this.profileModel.length) {
      profileComplete = 'All';
    }

    this.articlesFirmSelectedName = [];
    this.articlesFirmPredefined.forEach((index,i) => {
      this.articlesFirmSelectedName.push(this.articlesFirmPredefined[i]);
    });
    this.articlesFirmSelectedName = (this.articlesFirmSelectedName.length === this.articles.length)
      ? new Array('All')
      : this.articlesFirmSelectedName;
    articlesFirm = (this.articlesFirmSelectedName.length > 0) ? this.articlesFirmSelectedName.join(',') : 'All';

    const params = {
      search: search,
      articlesFirm: articlesFirm,
      nationality: nationality,
      ethnicity: ethnicity,
      gender: gender,
      qualification: qualification,
      location: location,
      criminal: criminal,
      credit: credit,
      availability: availability,
      video: video,
      enabled: enabled,
      page: 1,
      articlesCompletedStart: articlesCompletedStart,
      articlesCompletedEnd: articlesCompletedEnd,
      profileComplete: profileComplete,
    };

    const startDate = new Date(articlesCompletedStart);
    const endDate = new Date(articlesCompletedEnd);
    if (startDate > endDate) {
      this._toastr.error('Date Articles Completed End not be shorter than the Date Articles Completed Start');
    }
    else{
      try {
        const response = await this._adminService.getAllCandidateListCount(params);
        this.candidatesCountMatchingCriteria = response.candidateCount;
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
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
   * Delete candidate profile
   * @param id
   * @return {Promise<void>}
   */
  public async deleteCandidateProfile(id): Promise<void> {
    try {
      await this._adminService.deleteCandidateProfile(id);

      this.approveCandidateList = this.approveCandidateList.filter((listElement) => listElement.id !== id);
      this.modalActiveClose.dismiss();
      this._sharedService.sidebarAdminBadges.candidateAll--;
      this._toastr.success('Candidate has been deleted');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update candidate status
   * @param id {number}
   * @param enabled {boolean}
   * @return {Promise<void>}
   */
  public async updateCandidateStatus(id: number, enabled) {
    enabled = !enabled;
    try {
      await this._adminService.updateCandidateStatus(id, enabled);
      this._toastr.success('Candidate status has been changed');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param id {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  id: number): void {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedId = id;
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   */
  public openVerticallyCenter(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'sm' });
  }

}
