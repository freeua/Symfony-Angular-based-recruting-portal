import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
import { BusinessService } from '../../../../services/business.service';
import { BusinessCandidate, JobCriteria } from '../../../../../entities/models';
import { SharedService } from '../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-browse-all-candidates-view-details-popup',
  templateUrl: './browse-all-candidates-view-details-popup.component.html',
  styleUrls: ['./browse-all-candidates-view-details-popup.component.scss']
})
export class BrowseAllCandidatesViewDetailsPopupComponent implements OnInit {
  public _candidateToView: any;
  public _listOfJobs: JobCriteria[];
  public _listJob = [];
  public checkTitle: boolean;
  public _viewPopup;
  public _totalCount;

  @Input() closePopup;
  @Input('candidateToView') set candidateToView(candidateToView) {
  if (candidateToView) {
    this._candidateToView = candidateToView;
    if (candidateToView.candidateID){
      this.getSpecificCandidateProfile(this._candidateToView.candidateID, this._candidateToView.jobID);
      this.checkTitle = true;
    }
    else {
      this.getSpecificCandidateProfile(this._candidateToView.id, null);
      this.checkTitle = false;
    }
  }
  }
  get candidateToView() {
  return this._candidateToView;
  }

  @Input('listJob') set listJob(listJob) {
    if (listJob) {
      this._listJob = listJob;
    }
  }

  @Input('totalCount') set totalCount(totalCount) {
    if (totalCount) {
      this._totalCount = totalCount;
    }
  }
  get totalCount() {
    return this._totalCount;
  }

  @Input('viewPopup') set viewPopup(viewPopup) {
    if (viewPopup) {
      this._viewPopup = viewPopup;
    }
  }
  get viewPopup() {
    return this._viewPopup;
  }

  @Input('listOfJobs') set listOfJobs(listOfJobs) {
    if (listOfJobs) {
      this._listOfJobs = listOfJobs;

    }
  }
  get listOfJobs() {
    return this._listOfJobs;
  }

  @ViewChild('videoPlayer') videoPlayer: ElementRef;
  @ViewChild('removeShortList') btnRemoveShortList: ElementRef;
  public candidate: BusinessCandidate;
  public nationality: string;
  public recentEmployer: string;
  public recentRole: string;
  public academicCertificates: object[] = [];
  public academicTranscripts: object[] = [];
  public creditChecks: object[] = [];
  public availability: string;
  public boards: string;
  public cv: object;
  public cvFiles: object[] = [];
  public groupOfButtonstoShow: number;
  public modalActiveClose;
  public loaderPopup = true;
  public listOfJobsCount: JobCriteria[];

  public criminalMore = false;
  public creditMore = false;

  public bussinesFirstName: string;

  constructor(
    private readonly _businessService: BusinessService,
    private readonly  _sharedService: SharedService,
    private readonly  _toastr: ToastrService,
    private readonly _modalService: NgbModal
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    if (this._candidateToView.candidateID){
      this.setStatusCandidateProfile(this._candidateToView.candidateID, 'view');
    }
    else {
      this.getBusinessProfileDetails().then(() => {
          this.setStatusCandidateProfile(this._candidateToView.id, 'view');
      });
    }
  }

  /**
   * Get details profile business
   * @return {Promise<void>}
   */
  public async getBusinessProfileDetails(): Promise<void> {
    const data = await this._businessService.getBusinessProfile();
    this.bussinesFirstName = data.profile.user.firstName;
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

  public moreCriminal(): void{
    this.criminalMore = !this.criminalMore;
  }

  public moreCredit(): void{
    this.creditMore = !this.creditMore;
  }

  /**
   * Get jobs
   * @param status {boolean}
   * @param candidateID {number}
   * @return {Promise<void>}
   */
  public async getJobsCount(status, candidateID): Promise<void>{
    try {
      this.listOfJobsCount = await this._businessService.getBusinessJobsMatchingCriteria(status, candidateID);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Change remove short list
   * @param status {boolean}
   */
  public changeRemoveShortList(status): void{
    if(status === true){
      this.btnRemoveShortList.nativeElement.classList.remove('btn-warning');
      this.btnRemoveShortList.nativeElement.classList.add('btn-danger');
      this.btnRemoveShortList.nativeElement.innerHTML = 'Remove from Short list <i class="fa fa-times"></i>';
    }
    else{
      this.btnRemoveShortList.nativeElement.classList.remove('btn-danger');
      this.btnRemoveShortList.nativeElement.classList.add('btn-warning');
      this.btnRemoveShortList.nativeElement.innerHTML = 'Short list <i class="fa fa-check"></i>';
    }
  }

  /**
  * gets candidate details specified with id
  * @param id {number} - candidate id
  * @param jobID {number} - candidate id
  * @returns void
  */
  public async getSpecificCandidateProfile(id: number, jobID): Promise<void> {
    try {
      this.candidate = await this._businessService.getCandidateById(id, jobID);

      const nationality = this._sharedService.getNationalityInHumanReadableForm(this.candidate.details.nationality);
      this.nationality = (nationality) ? nationality : '-';
      this.availability = this._sharedService.getCandidateAvailabilityInHumanReadableForm(
        this.candidate.details.availability, this.candidate.details.availabilityPeriod, this.candidate.details.dateAvailability
      );
      this.boards = this._sharedService.getBoardsInHumanReadableForm(this.candidate.details.boards);
      this.recentEmployer = (this.candidate.details.mostEmployer) ? this.candidate.details.mostEmployer : '-';
      this.recentRole = (this.candidate.details.mostRole) ? this.candidate.details.mostRole : '-';
      const metricCertificates = this.candidate.details.matricCertificate;
      const tertiaryCertificates = this.candidate.details.tertiaryCertificate;
      if(metricCertificates && tertiaryCertificates){
        this.academicCertificates = metricCertificates.concat(tertiaryCertificates);
      }
      else if(metricCertificates){
        this.academicCertificates = metricCertificates;
      }
      else if(tertiaryCertificates){
        this.academicCertificates = tertiaryCertificates;
      }
      this.academicCertificates = this.academicCertificates.filter((certificate) => certificate['approved'] === true);
      if(this.candidate.details.universityManuscript){
        this.academicTranscripts = this.candidate.details.universityManuscript.filter((certificate) => certificate['approved'] === true);
      }
      if(this.candidate.details.creditCheck){
        this.creditChecks = this.candidate.details.creditCheck.filter((check) => check.approved === true);
      }
      if(this.candidate.details.cvFiles){
        this.cvFiles = this.candidate.details.cvFiles;
        this.cv = this.candidate.details.cvFiles.filter((file) => file.approved === true).pop();
      }
      setTimeout(() => {
        this.loaderPopup = false;
      }, 1000);
      this.groupOfButtonstoShow = this.candidate.applicant;
      this.videoPlayer.nativeElement.load();


    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
  * Add Candidate to Short List
  * @param candidate {object}
  * @returns {Promise<void>}
  */
  public async addCandidateToShortList(candidate): Promise<void>{
    try {
      await this._businessService.addCandidateToShortList(candidate.details.id, candidate.jobID);
      if(this._viewPopup !== 'true'){
        if(candidate.applicant === 1 || candidate.applicant === 4) {
          const index = this._listJob.indexOf(this._candidateToView);
          this._listJob.splice(index, 1);
          this._totalCount.number-=1;
        }
      }
      else{
        if(candidate.applicant === 3 || candidate.applicant === 6 || candidate.applicant === 7){
          this._sharedService.sidebarBusinessBadges.applicantShortlist++;
          this._sharedService.sidebarBusinessBadges.applicantAll++;
        }
      }
      if(candidate.applicant === 0){
        this._sharedService.sidebarBusinessBadges.applicantShortlist++;
        this._sharedService.sidebarBusinessBadges.applicantAll++;
      }
      else if(candidate.applicant === 1){
        this._sharedService.sidebarBusinessBadges.applicantAwaiting--;
        this._sharedService.sidebarBusinessBadges.applicantShortlist++;
      }
      else if(candidate.applicant === 4){
        this._sharedService.sidebarBusinessBadges.applicantDecline--;
        this._sharedService.sidebarBusinessBadges.applicantShortlist++;
      }

      this.closePopup();
      this._toastr.success('Added to ShortList');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove Candidate From ShortList
   * @param {number} candidate
   * @returns {Promise<void>}
   */
  public async removeCandidateFromShortList(candidate): Promise<void>{
    try {
      await this._businessService.removeCandidateFromShortList(candidate.details.id, candidate.jobID);
      this.closePopup();
      this._listJob = this._listJob.filter((response) => response !== candidate);
      this._sharedService.sidebarBusinessBadges.applicantShortlist--;
      this._sharedService.sidebarBusinessBadges.applicantAll--;
      this._toastr.success('Removed from ShortList');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Add Candidate to Short List
   * @param candidate
   * @returns {Promise<void>}
   */
  public async declineCandidateApplication(candidate): Promise<void>{

    try {
      await this._businessService.declineCandidateApplication(candidate.details.id, candidate.jobID);

      const index = this._listJob.indexOf(this._candidateToView);
      this._listJob.splice(index, 1);
      this._totalCount.number-=1;
      if(candidate.applicant === 1){
        this._sharedService.sidebarBusinessBadges.applicantAwaiting--;
        this._sharedService.sidebarBusinessBadges.applicantDecline++;
      }
      else if(candidate.applicant === 2){
        this._sharedService.sidebarBusinessBadges.applicantShortlist--;
        this._sharedService.sidebarBusinessBadges.applicantDecline++;
      }
      this.closePopup();
      this._toastr.success('Application was declined');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Cancel Application From Business
   * @param candidate
   * @returns {Promise<void>}
   */
  public async cancelApplicationFromBusiness(candidate): Promise<void>{

    try {
      await this._businessService.cancelApplicationFromBusiness(candidate.details.id, candidate.jobID);
      this.closePopup();
      this._listJob = this._listJob.filter((response) => response !== candidate);
      this._toastr.success('You application for interview was canceled');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Add candidate to interview
   * @param candidate {object}
   * @param jobID {number}
   * @return {Promise<void>}
   */
  public async setUpInterview(candidate, jobID): Promise<void> {
    try {
      const check = await this._businessService.setUpInterviewCandidate(candidate.details.id, Number(jobID));

      if(check.check === 1){
        this._sharedService.sidebarBusinessBadges.applicantAll++;
        this._sharedService.sidebarBusinessBadges.applicantApprove++;
      } else if(check.check === 2){
        this._sharedService.sidebarBusinessBadges.applicantAll++;
        this._sharedService.sidebarBusinessBadges.applicantApprove++;
      } else if(check.check === 3){

      }

      if(this._viewPopup !== 'true'){
        if(candidate.applicant === 1 || candidate.applicant === 2 || candidate.applicant === 4) {
          const index = this._listJob.indexOf(this._candidateToView);
          this._listJob.splice(index, 1);
          this._totalCount.number-=1;
        }

        if(candidate.applicant === 4){
          this._sharedService.sidebarBusinessBadges.applicantDecline--;
          this._sharedService.sidebarBusinessBadges.applicantApprove++;
        }
      }
      else{
        if(candidate.applicant === 4){
          if(candidate.jobID === Number(jobID)){
            this._sharedService.sidebarBusinessBadges.applicantDecline--;
            this._sharedService.sidebarBusinessBadges.applicantApprove++;
          }
        }
      }

      if(candidate.applicant === 1){
        this._sharedService.sidebarBusinessBadges.applicantAwaiting--;
        this._sharedService.sidebarBusinessBadges.applicantApprove++;
      }
      else if(candidate.applicant === 2){
        this._sharedService.sidebarBusinessBadges.applicantShortlist--;
        this._sharedService.sidebarBusinessBadges.applicantApprove++;
      }
      this._toastr.success('Success');
      this.modalActiveClose.dismiss();
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set status candidate profile
   * @param candidateID {number}
   * @param action {string}
   * @return {Promise<void>}
   */
  public async setStatusCandidateProfile(candidateID: number, action: string): Promise<void> {
    try {
      await this._businessService.setStatusCandidateProfile(candidateID, action);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * opens popup
   * @param content - content to be placed within
   * @param candidate
   */
  public openVerticallyCentered(content: any, candidate) {

    if (candidate.jobID !== null && !this._viewPopup && candidate.applicant !== 6 && candidate.applicant !== 7) {
      this.setUpInterview(candidate, candidate.jobID);
    }
    else {
      this.modalActiveClose = this._modalService.open(content, { centered: true, windowClass: 'second-popup', backdropClass: 'light-blue-backdrop' });
    }

    this.closePopup();

  }

  /**
   * Open modal
   * @param content
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, windowClass: 'second-popup', 'size': 'lg' });
  }
}
