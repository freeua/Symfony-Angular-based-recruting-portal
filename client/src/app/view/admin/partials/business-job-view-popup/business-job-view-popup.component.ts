import { Component, Input, OnInit } from '@angular/core';
import { BusinessAdminJobFullDetails } from '../../../../../entities/models';
import { SharedService } from '../../../../services/shared.service';
import { IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { IMultiSelectOption } from 'ng2-multiselect';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { INgxMyDpOptions } from 'ngx-mydatepicker';
import { ToastrService } from 'ngx-toastr';
import { closureDateValidator } from '../../../../validators/custom.validator';
import { articles } from '../../../../constants/articles.const';
import { AdminService } from '../../../../services/admin.service';
import { industry } from '../../../../constants/industry.const';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-business-job-view-popup',
  templateUrl: './business-job-view-popup.component.html',
  styleUrls: ['./business-job-view-popup.component.scss']
})
export class BusinessJobViewPopupComponent implements OnInit {
  @Input('selectedBusinessJob') selectedBusinessJob;
  @Input('selectedBusinessJobArray') selectedBusinessJobArray;
  @Input('selectedBusinessJobStatus') selectedBusinessJobStatus;
  @Input('closePopup') closePopup;
  public myOptions: INgxMyDpOptions = { dateFormat: 'yyyy/mm/dd' };
  public businessJobsForm: FormGroup;
  public specifiedBusinessJob = new BusinessAdminJobFullDetails({});
  public articles = articles;
  public businessJobObject: BusinessAdminJobFullDetails;
  public candidatesMatchingCriteria = 0;
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
    allSelected: 'Articles firm - All selected',
  };

  public preloaderPopup = true;

  public articlesFirmTextConfigBus: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Industry',
    allSelected: 'All selected',
  };
  public articlesFirmSettingsBus: IMultiSelectSettings = {
    displayAllSelectedText: true,
    selectionLimit: 0,
    showCheckAll: true,
    showUncheckAll: true,
  };
  public optionsModelBus: string[];
  public indistrySelect: IMultiSelectOption[] = industry;
  public modalActiveClose: any;

  public confirmFunction: string;
  public confirmData: any;
  public confirmStatus: any;
  public confirmArray: any;

  public dataFile: any;
  public fileIndex: any;
  public checkDataFile: boolean;

  constructor(
      private readonly _formBuilder: FormBuilder,
      private readonly _adminService: AdminService,
      private readonly _toastr: ToastrService,
      private readonly _sharedService: SharedService,
      private readonly _modalService: NgbModal
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    this.articles.forEach((article) => {
      this.articlesFirmOptions.push({ id: article, name: article });
    });
    this.getJobsForId();

    this.businessJobObject = new BusinessAdminJobFullDetails({});
    this.createJobsForm();
  }

  /**
   * Open confirm popup
   * @param content
   * @param confirmArray
   * @param nameFunction
   * @param data
   * @param status
   */
  public openConfirm(content: any, confirmArray, nameFunction, status): void {
    this.modalActiveClose = this._modalService.open(content, { centered: true, windowClass: 'second-popup', 'size': 'sm' });
    this.confirmFunction = nameFunction;
    this.confirmData = this.selectedBusinessJob;
    this.confirmStatus = status;
    this.confirmArray = this.selectedBusinessJobArray;
    this.closePopup();
  }


  /**
   * Open modal
   * @param content
   * @param data
   * @param index
   * @param status
   */
  public openVerticallyCenterFileClient(content, data, index, status) {
    this.dataFile = data;
    this.checkDataFile = status;
    this.fileIndex = index;
    this.modalActiveClose = this._modalService.open(content, { centered: true, windowClass: 'second-popup', 'size': 'lg' });
  }
  /**
   * Upload admin files for client
   * @param jobId
   * @param fileIndex
   * @param index
   * @param fileName
   * @returns {Promise<void>}
   */
  public async uploadAdminFilesClient(jobId, fileIndex, index, fileName): Promise<any> {


    let elem;
    if(!fileName) {
      elem = (<HTMLInputElement>document.getElementById(fileIndex));
    } else {
      elem = (<HTMLInputElement>document.getElementById(fileName));
    }
    const formData = new FormData();
    if(elem.files.length > 0){
      formData.append('spec', elem.files[0]);
    }

    try {
      const data = await this._adminService.uploadAdminFilesForClientAdmin(formData, jobId);
      this.specifiedBusinessJob['spec'] = data['spec'];
      this.modalActiveClose.dismiss();
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get jobs for ID
   * @return {Promise<void>}
   */
  public async getJobsForId(): Promise <void>{
    try {
      const response = await this._adminService.getBusinessJobById(this.selectedBusinessJob.id);
      this.specifiedBusinessJob = response;
      if (this.specifiedBusinessJob.qualification === 0) {
        this.specifiedBusinessJob.qualification = 1;
      }
      setTimeout(() => {
        this.fetchCandidatesByCriteria(
          this.specifiedBusinessJob.articlesFirm,
          this.specifiedBusinessJob.gender,
          this.specifiedBusinessJob.qualification,
          this.specifiedBusinessJob.nationality,
          this.specifiedBusinessJob.ethnicity,
          this.specifiedBusinessJob.video,
          this.specifiedBusinessJob.location,
          this.specifiedBusinessJob.availability
        );
      }, 200);
      this.businessJobsForm.controls['closureDate'].setValidators([
        Validators.required,
        closureDateValidator(new Date())
      ]);
      this.populateFormWithData();
      this._sharedService.fetchGoogleAutocompleteDetails(this.businessJobsForm);
    }
    catch (err){
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * populates form with data
   * @returns void
   */
  public populateFormWithData(): void {
    this.specifiedBusinessJob.articlesFirm.forEach((firm) => {
      if (firm === 'All') {
        this.articlesFirmPredefined = this.articles;
      } else {
        this.articlesFirmPredefined.push(firm);
      }
    });
    const companyDescription = (this.specifiedBusinessJob.companyDescriptionChange)
        ? this.specifiedBusinessJob.companyDescriptionChange
        : this.specifiedBusinessJob.companyDescription;
    const roleDescription = (this.specifiedBusinessJob.roleDescriptionChange)
        ? this.specifiedBusinessJob.roleDescriptionChange
        : this.specifiedBusinessJob.roleDescription;
    this.businessJobsForm.controls['jobTitle'].setValue(this.specifiedBusinessJob.jobTitle);
    this.businessJobsForm.controls['industry'].setValue(this.specifiedBusinessJob.industry);
    this.businessJobsForm.controls['companyName'].setValue(this.specifiedBusinessJob.companyName);
    this.businessJobsForm.controls['address'].setValue(this.specifiedBusinessJob.companyAddress);
    this.businessJobsForm.controls['addressCountry'].setValue(this.specifiedBusinessJob.addressCountry);
    this.businessJobsForm.controls['addressState'].setValue(this.specifiedBusinessJob.addressState);
    this.businessJobsForm.controls['addressCity'].setValue(this.specifiedBusinessJob.addressCity);
    this.businessJobsForm.controls['addressSuburb'].setValue(this.specifiedBusinessJob.addressSuburb);
    this.businessJobsForm.controls['addressZipCode'].setValue(this.specifiedBusinessJob.addressZipCode);
    this.businessJobsForm.controls['addressStreet'].setValue(this.specifiedBusinessJob.addressStreet);
    this.businessJobsForm.controls['addressStreetNumber'].setValue(this.specifiedBusinessJob.addressStreetNumber);
    this.businessJobsForm.controls['addressBuildName'].setValue(this.specifiedBusinessJob.addressBuildName);
    this.businessJobsForm.controls['addressUnit'].setValue(this.specifiedBusinessJob.addressUnit);
    this.businessJobsForm.controls['companyDescription'].setValue(this.specifiedBusinessJob.companyDescription);
    this.businessJobsForm.controls['companyDescriptionChange'].setValue(companyDescription);
    this.businessJobsForm.controls['roleDescription'].setValue(this.specifiedBusinessJob.roleDescription);
    this.businessJobsForm.controls['roleDescriptionChange'].setValue(roleDescription);
    this.businessJobsForm.controls['closureDate'].setValue(this.specifiedBusinessJob.closureDate);
    const date = new Date(this.specifiedBusinessJob.closureDate);
    this.businessJobsForm.patchValue({closureDate: {
      date: {
        year: date.getFullYear(),
        month: date.getMonth() + 1,
        day: date.getDate(),
      }
    }});
    this.businessJobsForm.controls['started'].setValue(this.specifiedBusinessJob.started);
    if (this.specifiedBusinessJob.started) {
      const started = new Date(this.specifiedBusinessJob.started);
      this.businessJobsForm.patchValue({started: {
        date: {
          year: started.getFullYear(),
          month: started.getMonth() + 1,
          day: started.getDate(),
        }
      }});
    }
    this.businessJobsForm.controls['filled'].setValue(this.specifiedBusinessJob.filled);
    if (this.specifiedBusinessJob.filled) {
      const filled = new Date(this.specifiedBusinessJob.filled);
      this.businessJobsForm.patchValue({filled: {
        date: {
          year: filled.getFullYear(),
          month: filled.getMonth() + 1,
          day: filled.getDate(),
        }
      }});
    }
    this.businessJobsForm.controls['articlesFirm'].setValue(this.articlesFirmPredefined);
    this.businessJobsForm.controls['gender'].setValue(this.specifiedBusinessJob.gender);
    this.businessJobsForm.controls['ethnicity'].setValue(this.specifiedBusinessJob.ethnicity);
    this.businessJobsForm.controls['nationality'].setValue(this.specifiedBusinessJob.nationality);
    // this.businessJobsForm.controls['video'].setValue(this.specifiedBusinessJob.video);
    this.businessJobsForm.controls['availability'].setValue(this.specifiedBusinessJob.availability);
    this.businessJobsForm.controls['location'].setValue(this.specifiedBusinessJob.location);
    this.businessJobsForm.controls['qualification'].setValue(this.specifiedBusinessJob.qualification);
  }

  /**
   * creates businessJobsForm and populates with specified data
   * @returns void
   */
  public createJobsForm(): void {
    this.businessJobsForm = this._formBuilder.group({
      jobTitle: [null, Validators.required],
      industry: [null, Validators.required],
      companyName: [null, Validators.required],
      address: [null, Validators.required],
      addressCountry: [null],
      addressState: [null],
      addressZipCode: [null],
      addressCity: [null],
      addressSuburb: [null],
      addressStreet: [null],
      addressStreetNumber: [null],
      addressBuildName: [null],
      addressUnit: [null],
      companyDescription: [null, null],
      companyDescriptionChange: [null, Validators.compose([
        Validators.required,
        Validators.maxLength(300),
      ])],
      roleDescription: [null, null],
      roleDescriptionChange: [null, Validators.compose([
        Validators.required,
        Validators.maxLength(400),
      ])],
      closureDate: [null, null],
      started: [null, Validators.required],
      filled: [null],
      articlesFirm: [null, Validators.required],
      gender: [null, Validators.required],
      ethnicity: [null, Validators.required],
      nationality: [null, Validators.required],
      // video: [null, Validators.required],
      availability: [null, Validators.required],
      location: [null, Validators.required],
      // post_articles: [null, ''],
      // salary_range: [null, ''],
      qualification: [1, Validators.required],
    });
  }

  /**
   * updates business job specified with id
   * @returns void
   */
  public processJobsEdit(): void {
    if (this.businessJobsForm.valid) {
      Object.keys(this.businessJobsForm.controls).forEach((key) => {
        const fieldName = (key === 'address') ? 'companyAddress' : key;
        switch (key) {
          case 'industry': {
            this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value;
            break;
          }
          case 'nationality':
          // case 'video':
          case 'availability':
          case 'qualification':
          case 'postArticles':
          case 'salaryRange': {
            this.businessJobObject[fieldName] = Number(this.businessJobsForm.controls[key].value);
            break;
          }
          case 'gender': {
            this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value;
            break;
          }
          case 'ethnicity': {
            this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value;
            break;
          }
          case 'location': {
            this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value.label;
            break;
          }
          case 'articlesFirm': {
            this.businessJobObject[fieldName] = this.articlesFirmSelectedName;
            break;
          }
          case 'closureDate': {
            const closureDate = this.businessJobsForm.controls[key].value;
            const year = closureDate.date.year;
            const month = closureDate.date.month;
            const date = (closureDate.date.day.length === 1) ? `0${closureDate.date.day}` : closureDate.date.day;
            this.businessJobObject[fieldName] = `${year}-${month}-${date}`;
            break;
          }
          case 'started': {
            const started = this.businessJobsForm.controls[key].value;
            const year = started.date.year;
            const month = started.date.month;
            const date = (started.date.day.length === 1) ? `0${started.date.day}` : started.date.day;
            this.businessJobObject[fieldName] = `${year}-${month}-${date}`;
            break;
          }
          case 'filled': {
            if(this.businessJobsForm.controls[key].value) {
              const filled = this.businessJobsForm.controls[key].value;
              const year = filled.date.year;
              const month = filled.date.month;
              const date = (filled.date.day.length === 1) ? `0${filled.date.day}` : filled.date.day;
              this.businessJobObject[fieldName] = `${year}-${month}-${date}`;
            } else {
              this.businessJobObject[fieldName] = null;
            }
            break;
          }
          default: {
            this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value;
            break;
          }
        }
      });
      this.businessJobObject.location = this.businessJobsForm.controls.location.value;
      this._adminService.updateBusinessJob(this.selectedBusinessJob.id, this.businessJobObject).then(
          (response) => {
            this._toastr.success('Business Job has been successfully updated!');
            this.closePopup();
          }
      ).catch((error) => {
        this._sharedService.showRequestErrors(error);
      });
    } else {
      this._sharedService.validateAllFormFields(this.businessJobsForm);
    }
  }

  /**
   * sets articles firm criteria
   * @param event
   * @param gender {string}
   * @param qualification {integer}
   * @param nationality {integer}
   * @param ethnicity {string}
   * @param video {integer}
   * @param location {string}
   * @param availability {integer}
   * @returns void
   */
  public async specifiedArticlesFirmCriteria(event, gender?, qualification?, nationality?, ethnicity?, video?, location?, availability?): Promise<void> {
    if (gender === undefined || gender === null) {
      gender = null;
    }
    else if (gender.label){
      gender = gender.label;
    }

    if (ethnicity === undefined || ethnicity === null) {
      ethnicity = null;
    }
    else if (ethnicity.label){
      ethnicity = ethnicity.label;
    }

    if (location === undefined || location === null) {
      location = null;
    }
    else if (location.label){
      location = location.label;
    }

    if (nationality === undefined || nationality === null) {
      nationality = null;
    }
    if (qualification === undefined || qualification === null) {
      qualification = null;
    }
    if (video === undefined || video === null) {
      video = null;
    }
    if (availability === undefined || availability === null) {
      availability = null;
    }
    try {
      this.articlesFirmSelectedName = [];
      if (this.articlesFirmPredefined !== null) {
        this.articlesFirmPredefined.forEach((index, i) => {
          this.articlesFirmSelectedName.push(this.articlesFirmPredefined[i]);
        });
      }
      this.articlesFirmSelectedName = (this.articlesFirmSelectedName.length === this.articles.length)
        ? new Array('All')
        : this.articlesFirmSelectedName;
      const articlesFirm = (this.articlesFirmSelectedName.length > 0) ? this.articlesFirmSelectedName.join(',') : null;
      const data = await this._adminService.getCandidatesCountSatisfyCriteria(articlesFirm, gender, qualification, nationality, ethnicity, video, location, availability);
      this.candidatesMatchingCriteria = data.countCandidate;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * fetches candidate by criteria
   * @param articlesFirm {string}
   * @param gender {string}
   * @param qualification {integer}
   * @param nationality {integer}
   * @param ethnicity {string}
   * @param video {integer]
   * @param location {string}
   * @param availability {integer}
   */
  public fetchCandidatesByCriteria(articlesFirm?, gender?, qualification?, nationality?, ethnicity?, video?, location?, availability?): void {

    if (gender === undefined || gender === null) {
      gender = null;
    }
    else if (gender.label){
      gender = gender.label;
    }
    if (ethnicity === undefined || ethnicity === null) {
      ethnicity = null;
    }
    else if (ethnicity.label){
      ethnicity = ethnicity.label;
    }
    if (nationality === undefined) {
      nationality = null;
    }
    if (location === undefined) {
      location = null;
    }
    else if (location.label){
      location = location.label;
    }
    if (qualification === undefined) {
      qualification = null;
    }
    if (video === undefined) {
      video = null;
    }
    if (availability === undefined) {
      availability = null;
    }
    this._adminService.getCandidatesCountSatisfyCriteria(
        articlesFirm, gender, qualification, nationality, ethnicity, video, location, availability).then((data) => {
      this.candidatesMatchingCriteria = data.countCandidate;
      this.preloaderPopup = false;
    }).catch((error) => {
      this._sharedService.showRequestErrors(error);
    });
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   */
  public openVerticallyCenter(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, windowClass: 'second-popup', backdropClass: 'light-blue-backdrop' });
  }
}
